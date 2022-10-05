<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Media\FileManager;


use App\Modules\Core\Commands\UpdateLocalDriveFilesInDb;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Media\FileManager\Exceptions\FileException;
use App\Modules\Media\Rules\MediaValidationRules;
use App\Modules\Media\States\DownloadFromURLState;
use App\Modules\Media\States\ExtractFileState;
use Devsrealm\TonicsFileManager\StorageDriver\StorageDriverInterface;
use Devsrealm\TonicsFileManager\Utilities\FileHelper;
use JetBrains\PhpStorm\NoReturn;

class LocalDriver implements StorageDriverInterface
{
    use FileHelper, Validator, MediaValidationRules;

    private string $path;

    public function __construct()
    {
        $this->path = DriveConfig::getPrivatePath();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function preFlight($data)
    {
        $tbl = $this->getBlobTable();
        $fileName = $this->normalizeFileName($data['Filename'], '_');
        $f = $data['Uploadto'] . DIRECTORY_SEPARATOR . $fileName;
        $chunksTemp = helper()
            ->generateBlobCollatorsChunksToSend($data['Byteperchunk'], $data['Totalblobsize'], $data['Chunkstosend'], $data['Uploadto'], $f);

        db()->insertOnDuplicate(
            table: $tbl, data: $chunksTemp, update: ['hash_id', 'moreBlobInfo'], chunkInsertRate: 2000
        );


        /**
         * RETURN ROWS THAT ARE EITHER CORRUPTED OR NEEDS TO BE FILLED
         * A DATA IS CORRUPTED IF missing_blob_chunk_byte is greater than 0 (The Missing Byte is Due To Connection Outage or Some Weird Shit)
         * A DATA Hasn't Been Filled If missing_blob_chunk_byte is null
         */
        $preflightData = db()
            ->run(<<<SQL
SELECT `id`, `blob_name`, `blob_chunk_part`, `blob_chunk_size`, `moreBlobInfo` 
                            FROM $tbl WHERE blob_name = ? AND missing_blob_chunk_byte IS NULL OR missing_blob_chunk_byte > 0;
SQL, $f);

        return ['preflightData' => $preflightData, 'filename' => $this->normalizeFileName($fileName, '_')];
    }

    /**
     * Download From URL
     * @param string $url
     * @param string $uploadTo
     * @param string $filename
     * @param bool $importToDB
     * @return bool
     * @throws \Exception
     */
    public function createFromURL(string $url, string $uploadTo = '', string $filename = '', bool $importToDB = true): bool
    {
        $downloadFromURLState = new DownloadFromURLState($this, $url, $uploadTo, $filename, $importToDB);
        $downloadFromURLState->setMessageDebug($this->messageDebug);

        $initState = $downloadFromURLState::InitialState;
        $downloadFromURLState->setCurrentState($initState)->runStates(false);
        return $downloadFromURLState->getStateResult() === SimpleState::DONE;
    }

    /**
     * @param string $pathToArchive
     * @param string $extractTo
     * @param string $archiveType
     * @param bool $importToDB
     * @return bool
     * @throws \Exception
     */
    public function extractFile(
        string $pathToArchive,
        string $extractTo,
        string $archiveType = 'zip',
        bool $importToDB = true,
    ): bool
    {
        if (strtolower($archiveType) === 'zip') {
            $extractFileState = new ExtractFileState($this); $lastExtractedFilePath = '';
            helper()->extractZipFile($pathToArchive, $extractTo, function ($extractedFilePath, $shortFilePath, $remaining) use ($importToDB, $extractFileState) {
                helper()->sendMsg('ExtractFileState', "Extracted $shortFilePath");
                helper()->sendMsg('ExtractFileState', "Remaining $remaining File(s)");
                if ($importToDB){
                    $extractFileState
                        ->setExtractedFilePath($extractedFilePath)
                        ->setCurrentState(ExtractFileState::ExtractFileStateInitial)
                        ->runStates(false);
                }
            });

            $isDone = $extractFileState->getStateResult() === SimpleState::DONE;
            if ($importToDB === false){
                return true;
            }

            return $isDone;
        }
        return false;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function create($data): mixed
    {
        if (!$dataInfo = $this->validateCreateOrUpdateFile($data)) {
            return false;
        }

        if ($dataInfo->info->isUploadCompleted) {
            $totalChunks = (int)$dataInfo->blobInfo->totalChunks;
            $blob_name = str_replace("{$this->getPath()}", '', $dataInfo->filePath);
            $this->deleteBlobs($totalChunks, $blob_name);

            if ($dataInfo->blobInfo->newFile) {
                $this->insertFileToDB($dataInfo->filePath, $dataInfo->blobInfo->uploadToID);
            }
        }
        return $dataInfo->info;
    }

    /**
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function createFolder($data): bool
    {
        $folderProperties = json_decode($data);
        try {
            $validator = $this->getValidator()->make($folderProperties, $this->mediaFolderCreateRule());
        } catch (\Exception) {
            return false;
        }

        # if validation fails
        if ($validator->fails()) {
            return false;
        }
        # construct path to upload file to
        $uploadTo = $this->getPath() . $folderProperties->uploadTo;
        # if destination doesnt exist
        if (!helper()->fileExists($uploadTo)) {
            return false;
        }
        # folderPath
        $folderPath = $uploadTo . DIRECTORY_SEPARATOR . $this->normalizeFileName($folderProperties->filename, ' ');
        if (@mkdir($folderPath)) {
            $this->insertFileToDB($folderPath, $folderProperties->uploadToID, 'directory');
            return true;
        }
        return false;
    }

    /**
     * @param $data
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function cancelFileCreate($data): mixed
    {
        $blobInfo = json_decode($data);
        $validator = $this->getValidator()->make($blobInfo, $this->mediaFileCancelUploadRule());

        if ($validator->fails()) {
            return false;
        }

        # construct path to upload file to
        $uploadTo = $this->getPath() . $blobInfo->uploadTo;
        # filepath
        $filePath = $uploadTo . DIRECTORY_SEPARATOR . $blobInfo->filename;
        if (!helper()->fileExists($filePath)) {
            return false;
        }
        $fileSize = helper()->fileSize($filePath);

        $blob_name = str_replace("{$this->getPath()}", '', $filePath);
        $this->deleteBlobs($blobInfo->totalChunks, $blob_name);

        #
        # If fileSize is equal to the totalBlobSize,
        # then it means, user cancelled the uploading late or user mistakenly uploaded file that already exist (in which case they quickly cancelled the operation)...
        # Either way, we won't delete (user can choose to delete the file explicity if they like)
        if ($fileSize === (int)$blobInfo->totalBlobSize) {
            return true;
        }
        # Else (fileSize is lesser than totalBlobSize), this signifies that the file is uploading but not completed
        #  we cancel and delete.
        return helper()->forceDeleteFile($filePath, 50);
    }

    /**
     * @param $data
     * @return bool|object
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function validateCreateOrUpdateFile($data): bool|object
    {
        # if key doesn't exist
        if (!helper()->getAPIHeaderKey('BlobDataInfo')) {
            return false;
        }

        $blobInfo = json_decode(helper()->getAPIHeaderKey('BlobDataInfo'));
        try {
            $validator = $this->getValidator()->make($blobInfo, $this->mediaFileCreateRule());
        } catch (\Exception) {
            return false;
        }

        # if validation fails
        if (!$validator->passes()) {
            return false;
        }

        # construct path to upload file to
        $uploadTo = $this->getPath() . $blobInfo->uploadTo;

        # if path doesn't exist
        if (!helper()->fileExists($uploadTo)) {
            return false;
        }

        # filepath
        $filePath = $uploadTo . DIRECTORY_SEPARATOR . $blobInfo->filename;

        # we insert chunk
        $this->insertBlobChunk($blobInfo, $data, $filePath);

        return (object)[
            // filepath is the file we are creating or updating path
            'filePath' => $filePath,
            // info contains information about the updating process
            'info' => $this->isBlobChunkUploadCompleted($blobInfo, $filePath),
            // blob info
            'blobInfo' => $blobInfo
        ];
    }


    /**
     * Insert File or Directory To DB.
     * @param $path
     * file path
     * @param $uploadToID
     * id of that to upload file to
     * @param string $fileType
     * file, or directory
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function insertFileToDB(
        $path,
        $uploadToID,
        string $fileType = 'file'
    ): void
    {
        $fileComposed = $this->composeFile($path, $uploadToID, $fileType);
        $exist = $this->doesFileOrDirectoryExistInDB($path, $uploadToID);
        if ($exist === false){
            db()->insertOnDuplicate(
                table: $this->getDriveTable(), data: $fileComposed, update: ['properties'], chunkInsertRate: 1
            );
        }

    }

    /**
     * @param string $path
     * @param int $uploadToID
     * @return bool
     * @throws \ReflectionException
     */
    public function doesFileOrDirectoryExistInDB(string $path, int $uploadToID): bool
    {
        $currentFileRelPath = helper()->getDriveSystemParentSignature(DriveConfig::getPrivatePath(), $path);
        $searchData = [
            'id' => $uploadToID,
            'path' => $currentFileRelPath,
            'query' => helper()->getFileName($path)
        ];
        $filePath = $searchData['path'] . DIRECTORY_SEPARATOR . $searchData['query'];
        $searchResult = $this->searchFiles($searchData);
        $exist = false;
        if (isset($searchResult['data']) && is_array($searchResult['data'])) {
            foreach ($searchResult['data'] as $file) {
                if ($file->filepath === $filePath && $file->filename === $searchData['query']) {
                    $exist = true;
                    break;
                }
            }
        }
        return $exist;
    }

    /**
     * If result is not false, you get the following object items:
     *
     * - urlPreview:  Suitable for preview
     *  - urlDownload: Suitable for download
     * @throws \Exception
     */
    public function convertFilePathToFileObject(string $fullPath)
    {

        if (helper()->fileExists($fullPath) === false){
            return false;
        }

        $currentFileRelPath = str_replace(DriveConfig::getPrivatePath(), '',  DriveConfig::getUploadsPath());
        $table = $this->getDriveTable();
        $rootID = db()->row("Select `drive_id` FROM $table WHERE `drive_parent_id` IS NULL");
        if (!isset($rootID->drive_id)){
            return false;
        }
        $filename = helper()->getFileName($fullPath);
        $rootID = $rootID->drive_id;
        $searchData = [
            'id' => $rootID,
            'path' => $currentFileRelPath,
            'query' => $filename
        ];
        $searchResult = $this->searchFiles($searchData);
        $filePath = str_replace(DriveConfig::getPrivatePath(), '',  $fullPath);
        if (isset($searchResult['data']) && is_array($searchResult['data'])) {
            foreach ($searchResult['data'] as $file) {
                if ($file->filepath === $filePath && $file->filename === $searchData['query']) {
                    $file->urlPreview = DriveConfig::serveFilePath() . "$file->drive_unique_id?render";
                    $file->urlDownload = DriveConfig::serveFilePath() . "$file->drive_unique_id";
                    return $file;
                }
            }
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public function insertFileToDBReturning(
        $path,
        $uploadToID,
        $fileType = 'file',
        $return = []
    ): \stdClass|bool
    {
        $file = $this->composeFile($path, $uploadToID, $fileType);
        return db()->insertReturning(
            $this->getDriveTable(), $file, $return, 'drive_id'
        );
    }

    /**
     * @throws \Exception
     */
    protected function composeFile($path, $uploadToID, $fileType)
    {
        $relPath = helper()->getDriveSystemRelativeSignature(DriveConfig::getPrivatePath(), $path);
        $uniqueID = hash('sha256', $relPath . random_int(0000000, PHP_INT_MAX));

        $filename = helper()->getFileName($path);

        $ext = ($fileType !== 'file') ? null : helper()->extension($path);
        $properties = [
            'ext' => $ext,
            'filename' => $filename,
            'size' => helper()->fileSize($path),
            "time_created" => helper()->getFileTimeCreated($path),
            "time_modified" => helper()->getFileTimeModified($path)
        ];
        helper()->moreFileProperties($path, $ext, $properties);
        return [
            "drive_parent_id" => $uploadToID,
            "drive_unique_id" => $uniqueID,
            "type" => $fileType,
            'filename' => $filename,
            "properties" => json_encode($properties),
            "security" => json_encode([
                "lock" => false,
                "password" => random_int(0000000, PHP_INT_MAX),
            ])
        ];
    }


    /**
     * Checks if blob chunks has been successfully merged and uploaded.
     *
     * It Returns the following object data on the current upload:
     *
     * - totalUploadedChunks: The total number of the uploaded blob chunks, not to be confused with the total chunks
     * - totalChunks: The total number blob chunks that would be uploaded
     * - uploadPercentage: The upload percentage
     * - isUploadCompleted: Return bool on whether the upload is completed or not
     * @param $blobInfo
     * @param $filePath
     * @return object
     * @throws \Exception
     */
    public function isBlobChunkUploadCompleted($blobInfo, $filePath): object
    {
        $blob_name = str_replace("{$this->getPath()}", '', $filePath);

        // Returns the count of rows that are either corrupted or needs to be filled
        // A data is corrupted if missing_blob_chunk_byte is greater than 0 (the missing byte is due to connection outage or some weird shit)
        // A data hasn't been filled if missing_blob_chunk_byte is null
        $totalUploadedChunks = db()
            ->run("SELECT count(*) as count FROM {$this->getBlobTable()} 
                            WHERE `blob_name` = ? AND missing_blob_chunk_byte = 0", $blob_name)[0];

        // The total no blob chunks that would be uploaded
        $totalChunks = (int)$blobInfo->totalChunks;
        // The chunks that has been uploaded so far
        $totalUploadedChunks = $totalUploadedChunks->count;

        $sum = db()
            ->run("SELECT SUM(blob_chunk_size) as sum FROM {$this->getBlobTable()} 
                            WHERE `blob_name` = ? AND missing_blob_chunk_byte = 0", $blob_name)[0];

        return (object)[
            'totalUploadedChunks' => $totalUploadedChunks,
            'totalChunks' => $totalChunks,
            'uploaded' => (isset($sum->sum)) ? helper()->formatBytes($sum->sum) : null,
            'uploadPercentage' => ($totalUploadedChunks / $totalChunks) * 100,
            'isUploadCompleted' => $totalUploadedChunks === $totalChunks,
        ];
    }

    /**
     * @param \stdClass $blobInfo
     * @param $data
     * @param $filePath
     * @throws \Exception
     */
    public function insertBlobChunk(\stdClass $blobInfo, $data, $filePath)
    {
        $tbl = $this->getBlobTable();
        $outFile = helper()->forceOpenFile($filePath);
        if ($outFile !== false) {
            // startSlice is the offset
            $seek = fseek($outFile, $blobInfo->startSlice, SEEK_CUR);
            if ($seek === -1) {
                throw new FileException("Failed to Seek File Pointer of `$filePath`");
            }
            $write = fwrite($outFile, $data);
            if ($write === false) {
                throw new FileException("Failed To write To File. `$filePath`");
            }
            fclose($outFile);
            $outFile = null;
        }

        /// this is from create method since we want it to be as fast as possible
        if (isset($blobInfo->id)) {
            db()->run("UPDATE $tbl SET `live_blob_chunk_size` = ?  WHERE `id` = ?;", strlen($data), $blobInfo->id);
        }
    }

    /**
     * @param $totalChunks
     * @param $blob_name
     * @param int $chunksToDeleteAtATime
     * @throws \Exception
     */
    public function deleteBlobs($totalChunks, $blob_name, int $chunksToDeleteAtATime = 500): void
    {
        $noOfTimesToLoop = ceil($totalChunks / $chunksToDeleteAtATime);
        for ($i = 1; $i <= $noOfTimesToLoop; $i++) {
            db()->run("DELETE FROM {$this->getBlobTable()} WHERE `blob_name` = ? ORDER BY `id` DESC LIMIT $chunksToDeleteAtATime", $blob_name);
        }
    }


    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function clear($data): mixed
    {

        $files = $data->files;
        $deleteFiles = 0;
        foreach ($files as $file) {
            $validator = $this->getValidator()->make($file, $this->mediaFileDeleteRule());
            if ($validator->fails()) {
                return false;
            }
            $fileAbsPath = helper()->dirname($this->getPath() . DIRECTORY_SEPARATOR . $file->file_path) . DIRECTORY_SEPARATOR . $file->filename;
            if (helper()->deleteFileOrDirectory($fileAbsPath)) {
                ++$deleteFiles;
            }

            db()->run(<<<SQL
DELETE FROM {$this->getDriveTable()} WHERE `drive_id` = ?
SQL, $file->drive_id);
        }
        return $deleteFiles;
    }

    /**
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function moveFiles($data): bool
    {
        $files = $data->files;
        $destinationFolder = $data->destination;
        try {
            $validator = $this->getValidator()->make($destinationFolder, $this->mediaFileMoveRule());
        } catch (\Exception) {
            return false;
        }

        if ($validator->fails()) {
            return false;
        }
        $dirAbsPath = helper()->dirname($this->getPath() . DIRECTORY_SEPARATOR . $destinationFolder->file_path) . DIRECTORY_SEPARATOR . $destinationFolder->filename;

        #
        # If the destination folder doesn't exist, return false, else, move on
        #
        if (!helper()->fileExists($dirAbsPath) && helper()->isDirectory($dirAbsPath)) {
            return false;
        }

        #
        # Loop over each files, check if they actually exist, and check for cyclic pasting
        #
        foreach ($files as $file) {
            try {
                $validator = $this->getValidator()->make($file, $this->mediaFileMoveRule());
            } catch (\Exception) {
                return false;
            }

            if ($validator->fails()) {
                return false;
            }

            $fileAbsPath = helper()->dirname($this->getPath() . DIRECTORY_SEPARATOR . $file->file_path) . DIRECTORY_SEPARATOR . $file->filename;
            if (!helper()->fileExists($fileAbsPath)) {
                return false;
            }

            if ($file->drive_id === $destinationFolder->drive_id) {
                return false;
            }
        }

        #
        # If we got here, we can start moving the files into the directory
        #
        foreach ($files as $file) {
            $fileAbsPath = helper()->dirname($this->getPath() . DIRECTORY_SEPARATOR . $file->file_path) . DIRECTORY_SEPARATOR . $file->filename;
            if (!is_writable($fileAbsPath)) {
                return false;
            }

            if (helper()->move($fileAbsPath, $dirAbsPath . DIRECTORY_SEPARATOR . $file->filename)) {
                db()->run("UPDATE {$this->getDriveTable()} SET `drive_parent_id` = ?
                                                        WHERE drive_id = ?", $destinationFolder->drive_id, $file->drive_id);
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function rename($data)
    {
        $validator = $this->getValidator()->make($data, $this->mediaRenameRules());

        if ($validator->fails()) {
            return false;
        }

        #
        # Get the former file name absolute path, and checks if it exist
        #
        $fileDir = helper()->dirname($this->getPath() . DIRECTORY_SEPARATOR . $data->file_path);
        $renameFileFrom = $fileDir . DIRECTORY_SEPARATOR . $data->filename;
        if (helper()->fileExists($renameFileFrom)) {

            #
            # This extracts the filename from the new name, e.g
            # my-new-music.mp3 returns my-new-music, and if file
            # is a directory, e.g, my-directory, it returns the name as is
            #
            $newFilename = $this->normalizeFileName(helper()->getFileName($data->filename_new), '_');

            #
            # This composes what the new file name absolute path should be like, e.g
            # /path/to/file/my-new-music
            #
            $toFileName = $fileDir . DIRECTORY_SEPARATOR . $newFilename;

            #
            # This phase uses the previous file to detect the new file extension, if it is a file,
            # we detect the extension, we then overwrite $toFileName with the proper ext.
            #
            # If extension is not recognized or disallowed, we return back
            #
            if (is_file($renameFileFrom)) {
                $uploadedFileMime = $this->detectMime($renameFileFrom);
                $ext = $this->getMimeHumanFriendlyFormat($uploadedFileMime);
                if (!$ext) {
                    return false;
                }
                $ext = '.' . $ext;
                $toFileName = $toFileName . $ext;
                $newFilename = $newFilename . $ext;
            }

            #
            # If file name doesn't differ, we return $data, no point in going further
            #
            if ($renameFileFrom === $toFileName) {
                return $data;
            }

            #
            # At this junction, we check if d previous path is writable, we then
            # go ahead and rename to new name
            #
            if (is_writable($renameFileFrom)) {
                $rename = @rename($renameFileFrom, $toFileName);
                if ($rename === false) {
                    return false;
                }
                # If the renamed file was successful
                if (helper()->fileExists($toFileName)) {
                    db()->run(<<<SQL
UPDATE {$this->getDriveTable()} SET `filename` = ?, properties = JSON_SET(properties, '$.time_modified', ?, '$.filename', ?)
WHERE drive_id = ?
SQL, $newFilename, filemtime($toFileName), $newFilename, $data->drive_id);

                    #
                    # No point in querying the database for the updated file, we overwrite the $data variable with the updated info and return it.
                    #
                    $data->filename = $newFilename;
                    $data->file_path = str_replace($this->getPath() . DIRECTORY_SEPARATOR, '', $toFileName);
                    $data->time_modified = filemtime($toFileName);

                    return $data;
                }
            }
        }
        return false;
    }

    /**
     * This method retrieve all the files based on either the path string or the path id
     * @inheritDoc
     * @throws \Exception
     */
    public function list($id, $path = null): array|bool
    {
        # remove forward and backward slash
        if ($path !== null) {
            $path = trim($path, '/');
        }

        # Get Path Location
        $filePath = $this->getPath() . DIRECTORY_SEPARATOR . $path;
        if ($path) {
            # Return if the path is not available
            if (!helper()->fileExists($filePath)) {
                return false;
            }

            # We got here, so, the path is available, if the $id is not available
            # get the file list by using the path trail
            if (!$id) {
                $pathDir = explode('/', $path);
                $childID = $this->findChildRealID($pathDir);

                if (!$childID) {
                    return false;
                }
                $filesFromPath = $this->getDriveIDFilesFromPathAndID($path, $childID);
                $folderID = $childID;
            } else {
                # ID is available from query string
                $filesFromPath = $this->getDriveIDFilesFromPathAndID($path, $id);
                $folderID = $id;
            }
            # Path is empty .. Get Default Root Files
        } else {
            $path = helper()->getFileName(DriveConfig::getUploadsPath());
            $rootID = db()->row("SELECT drive_id FROM {$this->getDriveTable()} WHERE `drive_parent_id` IS NULL");
            if (!$rootID) {
                return false;
            }
            $folderID = $rootID->drive_id;
            $filesFromPath = $this->getDriveIDFilesFromPathAndID($path, $rootID->drive_id);

        }
        return [
            'data' => $filesFromPath->data ?? [],
            'folderID' => $folderID,
            'folderPath' => $path,
            'next_page_url' => $filesFromPath->next_page_url ?? null,
            'has_more' => $filesFromPath->has_more ?? false,
        ];

    }

    /**
     * @throws \Exception
     */
    public function reIndex(): mixed
    {
        $updateDriveFiles = new UpdateLocalDriveFilesInDb();
        try {
            $updateDriveFiles->run([]);
        } catch (\Exception) {
            return false;
        }

        return $updateDriveFiles->passes();
    }

    /**
     * @throws \Exception
     */
    protected function getDriveIDFilesFromPathAndID($path, $id): ?object
    {
        $tableRows = db()->run("SELECT COUNT(*) AS 'r' FROM {$this->getDriveTable()} WHERE `drive_parent_id` = ?", $id)[0]->r;
        return db()->paginate(
            tableRows: $tableRows,
            callback: function ($perPage, $offset) use ($id, $path) {
                $columns = Tables::addColumnsToTable(
                    Tables::removeColumnFromTable(Tables::$DRIVE_SYSTEM_COLUMN, ['security', 'status']),
                    ["CONCAT(?, '/', filename) as filepath"], true);
                return db()
                    ->run("SELECT $columns FROM {$this->getDriveTable()} WHERE drive_parent_id = ? ORDER BY 'drive_id' LIMIT ? OFFSET ?", $path, $id, $perPage, $offset);
            }, perPage: url()->getParam('per_page', 50));
    }

    /**
     * @param $data
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function searchFiles($data): mixed
    {
        $validator = $this->getValidator()->make($data, $this->mediaFileSearchRule());
        if ($validator->fails()) {
            return false;
        }
        $id = $data['id'];
        $path = $data['path'];
        $search = $data['query'];
        $tableRows = db()->run(<<<SQL
SELECT COUNT(*) AS 'r' FROM {$this->getDriveTable()} WHERE filename LIKE CONCAT(?, '%')
SQL, $search)[0]->r;

        $searchFiles = db()->paginate(
            tableRows: $tableRows,
            callback: function ($perPage, $offset) use ($id, $path, $search) {

                /**
                 * We Search File Recursively With CTE, Note: Instead of Searching From The Root Parent, We start the Search By First Grabbing...
                 * The Result From The Like Operator, We Then Carry That Result Along With The Recursion, This Way, We Know Where The Search Term Came From
                 */
                return db()
                    ->run("
WITH RECURSIVE search_files_recursively AS (
  SELECT 
drive_id AS main_drive_id, 
drive_parent_id AS main_drive_parent_id, 
drive_unique_id AS main_drive_unique_id,
filename AS main_filename,
status AS main_status,
properties AS main_properties,
`type` AS main_type,
drive_id, drive_parent_id, drive_unique_id, filename, CAST(filename AS VARCHAR (255)) AS filepath, status, properties
  FROM {$this->getDriveTable()} WHERE filename LIKE CONCAT(?, '%')
  UNION ALL
  SELECT  main_drive_id, main_drive_parent_id, main_drive_unique_id, main_filename, main_status, main_properties, main_type,
  f.drive_id, f.drive_parent_id, f.drive_unique_id, f.filename, CONCAT(f.filename, '/', filepath),  f.status, f.properties 
  FROM {$this->getDriveTable()} AS f, search_files_recursively AS sr WHERE f.drive_id = sr.drive_parent_id
)
SELECT 
main_drive_id AS drive_id , 
main_drive_parent_id AS drive_parent_id, 
main_drive_unique_id AS drive_unique_id ,
main_filename AS filename, 
main_type AS `type`,
CONCAT(?, '/', filepath) AS filepath,
main_status AS status, 
main_properties AS properties 
FROM search_files_recursively WHERE drive_parent_id = ? LIMIT ? OFFSET ?;",
                        $search, $path, $id, $perPage, $offset);
            }, perPage: url()->getParam('per_page', 50));

        return [
            'data' => $searchFiles->data ?? [],
            'folderID' => $id,
            'folderPath' => $path,
            'next_page_url' => $searchFiles->next_page_url ?? null,
            'has_more' => $searchFiles->has_more ?? false,
        ];
    }

    /**
     * If file is not null you get array with items:
     *
     * - mainFile: the mainfile in object format
     * - fileSecurity: mainFile security settings in object format
     * - fullFilePath: The full file path location in string
     * - alias: alias path for nginx X-Accel
     * @param string $uniqueID
     * @return array|null
     * @throws \Exception
     */
    public function getInfoOfUniqueID(string $uniqueID): ?array
    {
        #
        # RECURSIVELY GET THE PARENT OF THE FILE,
        # I HAVE RESTRICTED THIS TO WORK ONLY FOR FILE, MEANING, YOU CAN ONLY DOWNLOAD A FILE AND NOT A FOLDER
        #
        $fileInfo = db()->run("WITH RECURSIVE child_to_parent AS (
  SELECT drive_id, drive_parent_id, drive_unique_id, filename, CAST(filename AS VARCHAR (255)) AS filepath, status, properties, security
  FROM {$this->getDriveTable()} WHERE drive_unique_id = ? AND type = 'file' UNION ALL 
  SELECT  f.drive_id, f.drive_parent_id, f.drive_unique_id, f.filename, CONCAT(f.filename, '/', filepath),  f.status, f.properties, f.security 
  FROM {$this->getDriveTable()} AS f INNER JOIN child_to_parent cp on f.drive_id = cp.drive_parent_id
) SELECT * FROM child_to_parent;", $uniqueID);

        if (!is_array($fileInfo) || empty($fileInfo)) {
            return null;
        }

        $mainFile = $fileInfo[0];
        $fileSecurity = json_decode($mainFile->security);
        $path = $fileInfo[array_key_last($fileInfo)];
        $fullFilePath = DriveConfig::getPrivatePath() . '/' . $path->filepath;
        $aliasPath = DriveConfig::xAccelDownloadFilePath() . $path->filepath;

        return [
            'mainFile' => $mainFile,
            'fileSecurity' => $fileSecurity,
            'fullFilePath' => $fullFilePath,
            'aliasPath' => $aliasPath,
        ];

    }

    /**
     * @param $id
     * @return void
     * @throws \Exception
     */
    #[NoReturn] public function serveFile($id): void
    {
        #
        # RECURSIVELY GET THE PARENT OF THE FILE,
        # I HAVE RESTRICTED THIS TO WORK ONLY FOR FILE, MEANING, YOU CAN ONLY DOWNLOAD A FILE AND NOT A FOLDER
        #
        $fileInfo = $this->getInfoOfUniqueID($id);

        if ($fileInfo === null) {
            SimpleState::displayUnauthorizedErrorMessage();
        }

        $fileSecurity = $fileInfo['fileSecurity'];
        if ($fileSecurity->lock && url()->getParam('key') !== $fileSecurity->password) {
            SimpleState::displayUnauthorizedErrorMessage();
        }

        $fullFilePath = $fileInfo['fullFilePath'];
        $aliasPath = $fileInfo['aliasPath'];

        if (helper()->fileExists($fullFilePath)) {
            $forceDownload = !url()->hasParam('render');
            $this->serveDownloadableFile($aliasPath, helper()->fileSize($fullFilePath), $forceDownload, mime_content_type($fullFilePath));
        }
        SimpleState::displayUnauthorizedErrorMessage();
    }

    /**
     * Get the ID of the last element in the $pathTrail,
     * Note this only works for Directory
     * @param array $pathTrail
     * Path Trail of the folder e.g. path trail of folder /var/www/downloads/music is ['uploads', 'www', 'downloads', 'music']
     * @param int|null $parentID
     * Where To Start The Recursion, This should usually be null meaning the root folder
     * @param int $maxDepth
     * Max Recursion Depth Limit Before Quiting
     * @return bool|int
     * @throws \Exception
     */
    public function findChildRealID(array $pathTrail, int $parentID = null, int $maxDepth = 10000): bool|int
    {

        $numberOfQ = $this->getQuestionMarks(sizeof($pathTrail));

        # Note: Ordering by the `drive_parent_id` gives us higher probability to find the childID quickly, it's a quick optimization but not necessary at all.
        $pathTrailRows = db()->run(<<<SQL
SELECT drive_id, drive_parent_id, filename  FROM {$this->getDriveTable()} 
WHERE type = 'directory' AND filename IN($numberOfQ) ORDER BY drive_parent_id
SQL, ...$pathTrail);

        if (empty($pathTrailRows)) {
            return false;
        }

        #
        # Say we have the following $pathTrail ['uploads', 'images', 'images']...
        # meaning the following /uploads/images/images, the only way to get to that path without confusing the recursion is to set a...
        # limit of when to break the recursion when the children found is equal to what we are expecting, and that is the use of...
        #  sizeof($pathTrail)
        #
        $noOfPath = sizeof($pathTrail);

        # Created an inline function for the recursion, this because I wanna be in control of...
        # how the child and depth is passed
        $childInPathTrail = $this->findChildInPathTrail($pathTrailRows, $noOfPath, $parentID, $maxDepth);

        if ($childInPathTrail && !key_exists($noOfPath - 1, $childInPathTrail)) {
            return false;
        }
        return $childInPathTrail[$noOfPath - 1]->drive_id;
    }

    /**
     * Note: We must pass the child by reference, this way, we can easily access its content in the recursive cycle
     * If you don't pass by reference we would only get the first item which is not what we want
     * @throws \Exception
     */
    private function findChildInPathTrail(array $pathTrailRows, $noOfPath, int $parentID = null, $maxDepth = 10000, &$child = [], $depth = 0): array
    {
        foreach ($pathTrailRows as $value) {
            if ($depth === $maxDepth) {
                throw new \Exception("Maximum Recursion Depth of $depth Reached");
            }

            if (sizeof($child) === $noOfPath) {
                break;
            }

            if ($value->drive_parent_id === $parentID) {
                $child[] = $value;
                $this->findChildInPathTrail($pathTrailRows, $noOfPath, $value->drive_id, $maxDepth, $child, $depth = $depth + 1);
            }
        }
        return $child;
    }


    public function reverseWords($str, $separator = '/'): string
    {
        return implode($separator, array_reverse(explode($separator, $str)));
    }

    /**
     * Returns question marks, e.g, if you pass 3, it returns "?,?,?"
     * @param int $marks
     * @return string
     */
    public function getQuestionMarks(int $marks): string
    {
        return implode(',', array_fill(0, $marks, '?'));
    }

    public function getBlobTable(): string
    {
        return Tables::getTable(Tables::DRIVE_BLOB_COLLATOR);
    }

    public function getDriveTable(): string
    {
        return Tables::getTable(Tables::DRIVE_SYSTEM);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}