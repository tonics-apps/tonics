<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class LocalDriver implements StorageDriverInterface
{
    use FileHelper, Validator, MediaValidationRules;

    private string        $path;
    private TonicsHelpers $helpers;

    /**
     * @throws \Exception
     */
    public function __construct (TonicsHelpers $helpers = null)
    {
        $this->path = DriveConfig::getPrivatePath();
        $this->helpers = $helpers ?? helper();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function preFlight ($data)
    {
        $tbl = $this->getBlobTable();
        $fileName = $this->normalizeFileName($data['Filename'], '_');
        $f = $data['Uploadto'] . DIRECTORY_SEPARATOR . $fileName;
        $chunksTemp = $this->helpers
            ->generateBlobCollatorsChunksToSend($data['Byteperchunk'], $data['Totalblobsize'], $data['Chunkstosend'], $data['Uploadto'], $f);

        $preflightData = null;
        db(onGetDB: function ($db) use ($f, $chunksTemp, $tbl, &$preflightData) {
            $db->insertOnDuplicate(
                table: $tbl, data: $chunksTemp, update: ['hash_id', 'moreBlobInfo'], chunkInsertRate: 2000,
            );

            /**
             * RETURN ROWS THAT ARE EITHER CORRUPTED OR NEEDS TO BE FILLED
             * A DATA IS CORRUPTED IF missing_blob_chunk_byte is greater than 0 (The Missing Byte is Due To Connection Outage or Some Weird Shit)
             * A DATA Hasn't Been Filled If missing_blob_chunk_byte is null
             */
            $preflightData = $db
                ->run(<<<SQL
SELECT `id`, `blob_name`, `blob_chunk_part`, `blob_chunk_size`, `moreBlobInfo` 
                            FROM $tbl WHERE blob_name = ? AND missing_blob_chunk_byte IS NULL OR missing_blob_chunk_byte > 0;
SQL, $f);

        });

        return ['preflightData' => $preflightData, 'filename' => $this->normalizeFileName($fileName, '_')];
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function create ($data): mixed
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
     *
     * @return bool
     * @throws \Exception
     */
    public function createFolder ($data): bool
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
        if (!$this->helpers->fileExists($uploadTo)) {
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
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function cancelFileCreate ($data): mixed
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
        if (!$this->helpers->fileExists($filePath)) {
            return false;
        }
        $fileSize = $this->helpers->fileSize($filePath);

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
        return $this->helpers->forceDeleteFile($filePath, 50);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function clear ($data): mixed
    {

        $files = $data->files;
        $deleteFiles = 0;
        foreach ($files as $file) {
            $validator = $this->getValidator()->make($file, $this->mediaFileDeleteRule());
            if ($validator->fails()) {
                return false;
            }
            $fileAbsPath = $this->helpers->dirname($this->getPath() . DIRECTORY_SEPARATOR . $file->file_path) . DIRECTORY_SEPARATOR . $file->filename;
            if ($this->helpers->deleteFileOrDirectory($fileAbsPath)) {
                ++$deleteFiles;
            }

            db(onGetDB: function ($db) use ($file) {
                $db->run(<<<SQL
DELETE FROM {$this->getDriveTable()} WHERE `drive_id` = ?
SQL, $file->drive_id);
            });
        }
        return $deleteFiles;
    }

    /**
     * @param $data
     *
     * @return bool
     * @throws \Exception
     */
    public function moveFiles ($data): bool
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
        $dirAbsPath = $this->helpers->dirname($this->getPath() . DIRECTORY_SEPARATOR . $destinationFolder->file_path) . DIRECTORY_SEPARATOR . $destinationFolder->filename;

        #
        # If the destination folder doesn't exist, return false, else, move on
        #
        if (!$this->helpers->fileExists($dirAbsPath) && $this->helpers->isDirectory($dirAbsPath)) {
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

            $fileAbsPath = $this->helpers->dirname($this->getPath() . DIRECTORY_SEPARATOR . $file->file_path) . DIRECTORY_SEPARATOR . $file->filename;
            if (!$this->helpers->fileExists($fileAbsPath)) {
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
            $fileAbsPath = $this->helpers->dirname($this->getPath() . DIRECTORY_SEPARATOR . $file->file_path) . DIRECTORY_SEPARATOR . $file->filename;
            if (!is_writable($fileAbsPath)) {
                return false;
            }

            if ($this->helpers->move($fileAbsPath, $dirAbsPath . DIRECTORY_SEPARATOR . $file->filename)) {
                db(onGetDB: function ($db) use ($file, $destinationFolder) {
                    $db->run("UPDATE {$this->getDriveTable()} SET `drive_parent_id` = ?
                                                        WHERE drive_id = ?", $destinationFolder->drive_id, $file->drive_id);
                });
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function rename ($data)
    {
        $validator = $this->getValidator()->make($data, $this->mediaRenameRules());

        if ($validator->fails()) {
            return false;
        }

        #
        # Get the former file name absolute path, and checks if it exist
        #
        $fileDir = $this->helpers->dirname($this->getPath() . DIRECTORY_SEPARATOR . $data->file_path);
        $renameFileFrom = $fileDir . DIRECTORY_SEPARATOR . $data->filename;
        if ($this->helpers->fileExists($renameFileFrom)) {

            #
            # This extracts the filename from the new name, e.g
            # my-new-music.mp3 returns my-new-music, and if file
            # is a directory, e.g, my-directory, it returns the name as is
            #
            $newFilename = $this->normalizeFileName($this->helpers->getFileName($data->filename_new), '_');

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
                if ($this->helpers->fileExists($toFileName)) {
                    db(onGetDB: function ($db) use ($data, $toFileName, $newFilename) {
                        $db->run(<<<SQL
UPDATE {$this->getDriveTable()} SET `filename` = ?, properties = JSON_SET(properties, '$.time_modified', ?, '$.filename', ?)
WHERE drive_id = ?
SQL, $newFilename, filemtime($toFileName), $newFilename, $data->drive_id);
                    });
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
    public function list ($id, $path = null): array|bool
    {
        # remove forward and backward slash
        if ($path !== null) {
            $path = trim($path, '/');
        }

        # Get Path Location
        $filePath = $this->getPath() . DIRECTORY_SEPARATOR . $path;
        if ($path) {
            # Return if the path is not available
            if (!$this->helpers->fileExists($filePath)) {
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
            $path = $this->helpers->getFileName(DriveConfig::getUploadsPath());
            $rootID = null;
            db(onGetDB: function ($db) use (&$rootID) {
                $rootID = $db->row("SELECT drive_id FROM {$this->getDriveTable()} WHERE `drive_parent_id` IS NULL");
            });
            if (!$rootID) {
                return false;
            }
            $folderID = $rootID->drive_id;
            $filesFromPath = $this->getDriveIDFilesFromPathAndID($path, $rootID->drive_id);

        }
        return [
            'data'          => $filesFromPath->data ?? [],
            'folderID'      => $folderID,
            'folderPath'    => $path,
            'next_page_url' => $filesFromPath->next_page_url ?? null,
            'has_more'      => $filesFromPath->has_more ?? false,
        ];

    }

    /**
     * @throws \Exception
     */
    public function reIndex (): mixed
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
     * @param $data
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function searchFiles ($data): mixed
    {
        $validator = $this->getValidator()->make($data, $this->mediaFileSearchRule());
        if ($validator->fails()) {
            return false;
        }

        $id = $data['id'];
        $path = $data['path'];

        $searchFiles = null;
        db(onGetDB: function ($db) use ($path, $id, $data, &$searchFiles) {
            $search = $data['query'];
            $tableRows = $db->run(<<<SQL
SELECT COUNT(*) AS 'r' FROM {$this->getDriveTable()} WHERE filename LIKE CONCAT(?, '%')
SQL, $search)[0]->r;

            $searchFiles = $db->paginate(
                tableRows: $tableRows,
                callback: function ($perPage, $offset) use ($id, $path, $search) {

                    /**
                     * We Search File Recursively With CTE, Note: Instead of Searching From The Root Parent, We start the Search By First Grabbing...
                     * The Result From The Like Operator, We Then Carry That Result Along With The Recursion, This Way, We Know Where The Search Term Came From
                     */
                    $cbData = null;
                    db(onGetDB: function ($db) use ($offset, $perPage, $id, $path, $search, &$cbData) {
                        $cbData = $db
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
                    });
                    return $cbData;
                }, perPage: url()->getParam('per_page', 50));
        });

        return [
            'data'          => $searchFiles->data ?? [],
            'folderID'      => $id,
            'folderPath'    => $path,
            'next_page_url' => $searchFiles->next_page_url ?? null,
            'has_more'      => $searchFiles->has_more ?? false,
        ];
    }

    /**
     * @param $id
     *
     * @return void
     * @throws \Exception
     */
    #[NoReturn] public function serveFile ($id): void
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

        if ($this->helpers->fileExists($fullFilePath)) {
            $forceDownload = !url()->hasParam('render');
            $this->serveDownloadableFile($aliasPath, $this->helpers->fileSize($fullFilePath), $forceDownload, mime_content_type($fullFilePath));
        }
        SimpleState::displayUnauthorizedErrorMessage();
    }

    /**
     * Download From URL
     *
     * @param string $url
     * @param string $uploadTo
     * @param string $filename
     * @param bool $importToDB
     *
     * @return bool
     * @throws \Exception
     */
    public function createFromURL (string $url, string $uploadTo = '', string $filename = '', bool $importToDB = true): bool
    {
        $downloadFromURLState = new DownloadFromURLState($this, $url, $uploadTo, $filename, $importToDB);

        $initState = $downloadFromURLState::InitialState;
        $downloadFromURLState->setCurrentState($initState)->runStates(false);
        return $downloadFromURLState->getStateResult() === SimpleState::DONE;
    }

    /**
     * @param string $filesPath
     *
     * @return bool
     */

    /**
     * @param string $pathToArchive
     * @param string $extractTo
     * @param string $archiveType
     * @param bool $importToDB
     *
     * @return bool
     * @throws \Exception
     */
    public function extractFile (
        string $pathToArchive,
        string $extractTo,
        string $archiveType = 'zip',
        bool   $importToDB = true,
    ): bool
    {
        if (strtolower($archiveType) === 'zip') {
            $extractFileState = new ExtractFileState($this);
            $lastExtractedFilePath = '';
            $this->helpers->extractZipFile($pathToArchive, $extractTo, function ($extractedFilePath, $shortFilePath, $remaining) use ($importToDB, $extractFileState) {
                $this->helpers->sendMsg('ExtractFileState', "Extracted $shortFilePath");
                $this->helpers->sendMsg('ExtractFileState', "Remaining $remaining File(s)");
                if ($importToDB) {
                    $extractFileState
                        ->setExtractedFilePath($extractedFilePath)
                        ->setCurrentState(ExtractFileState::ExtractFileStateInitial)
                        ->runStates(false);
                }
            });

            $isDone = $extractFileState->getStateResult() === SimpleState::DONE;
            if ($importToDB === false) {
                return true;
            }

            return $isDone;
        }
        return false;
    }

    /**
     * @param $data
     *
     * @return bool|object
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function validateCreateOrUpdateFile ($data): bool|object
    {
        # if key doesn't exist
        if (!$this->helpers->getAPIHeaderKey('BlobDataInfo')) {
            return false;
        }

        $blobInfo = json_decode($this->helpers->getAPIHeaderKey('BlobDataInfo'));
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
        if (!$this->helpers->fileExists($uploadTo)) {
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
            'info'     => $this->isBlobChunkUploadCompleted($blobInfo, $filePath),
            // blob info
            'blobInfo' => $blobInfo,
        ];
    }

    /**
     * Insert File or Directory To DB.
     *
     * @param $path
     * file path
     * @param $uploadToID
     * id of that to upload file to
     * @param string $fileType
     * file, or directory
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function insertFileToDB (
        $path,
        $uploadToID,
        string $fileType = 'file'
    ): void
    {
        $fileComposed = $this->composeFile($path, $uploadToID, $fileType);
        $exist = $this->doesFileOrDirectoryExistInDB($path, $uploadToID);
        if ($exist === false) {
            db(onGetDB: function ($db) use ($fileComposed) {
                $db->insertOnDuplicate(
                    table: $this->getDriveTable(), data: $fileComposed, update: ['properties'], chunkInsertRate: 1,
                );
            });
        }

    }

    /**
     * @throws \Exception
     */
    public function insertFileToDBReturning (
        $path,
        $uploadToID,
        $fileType = 'file',
        $return = []
    ): \stdClass|bool
    {
        $file = $this->composeFile($path, $uploadToID, $fileType);
        $result = null;
        db(onGetDB: function (TonicsQuery $db) use ($return, $file, &$result) {
            $result = $db->insertReturning(
                $this->getDriveTable(), $file, $return, 'drive_id',
            );
        });
        return $result;
    }

    /**
     * Note: For This function to work, the file or directory must have been created in the directory, so, this is just adding it to the database.
     *
     * This function is same as the one in ExtractFileStateInitial, so, would have to replace the one in ExtractFileStateInitial with this one
     *
     * - This function takes in a file path and recursively creates all the directories and files present in the path.
     * - It first extracts the path of files from the base path using DIRECTORY_SEPARATOR and then filters the empty values.
     * - The function then uses an array_map to loop through each file in the path and create directories if it does not exist already.
     * - It also checks if a file exists and if not, it inserts it into the database.
     * - The function returns true if the operation is successful, else returns false.
     *
     * @param string $filesPath The path of the files to be created.
     *
     * @return bool Returns true if the operation is successful, else returns false.
     */
    public function recursivelyCreateFileOrDirectory (string $filesPath, callable $onInsert = null): bool
    {
        $files = explode(DIRECTORY_SEPARATOR, str_replace(DriveConfig::getPrivatePath(), '', $filesPath));
        if (empty($files)) {
            return false;
        }

        $handledPath = [];
        $currentParent = null;
        $currentFileRelPath = '';
        $files = array_filter($files);
        try {
            array_map(function ($file) use ($onInsert, &$handledPath, &$currentParent, &$currentFileRelPath) {
                $currentFileRelPath .= DIRECTORY_SEPARATOR . $file;

                if (key_exists($currentFileRelPath, $handledPath)) {
                    $currentParent = $handledPath[$currentFileRelPath];
                }

                if ($this->helpers->isDirectory(DriveConfig::getPrivatePath() . $currentFileRelPath)) {
                    if (!key_exists($currentFileRelPath, $handledPath)) {
                        $pathID = $this->findChildRealID(array_filter(explode(DIRECTORY_SEPARATOR, $currentFileRelPath)));
                        ## Meaning No Such Path Exist
                        if ($pathID === false) {
                            $data = $this
                                ->insertFileToDBReturning(
                                    DriveConfig::getPrivatePath() . $currentFileRelPath,
                                    $currentParent->drive_id,
                                    'directory', ['drive_id']);
                            if ($onInsert) {
                                $onInsert($data);
                            }
                        } else {
                            $data = null;
                            db(onGetDB: function ($db) use ($pathID, &$data) {
                                $data = $db->row(<<<SQL
SELECT * FROM {$this->getDriveTable()} WHERE `drive_id` = ?
SQL, $pathID);
                            });
                        }
                        $handledPath[$currentFileRelPath] = $data;
                        $currentParent = $data;
                    }
                }

                if ($this->helpers->isFile(DriveConfig::getPrivatePath() . $currentFileRelPath)) {
                    $fileInserted = null;
                    $exist = $this->doesFileOrDirectoryExistInDB(
                        DriveConfig::getPrivatePath() . $currentFileRelPath,
                        $currentParent->drive_id,
                        function ($data) use (&$fileInserted) {
                            $fileInserted = $data;
                        });

                    if ($exist === false) {
                        $fileInserted = $this
                            ->insertFileToDBReturning(
                                DriveConfig::getPrivatePath() . $currentFileRelPath,
                                $currentParent->drive_id,
                                'file', ['drive_id', 'properties', 'drive_unique_id']);
                    }

                    if ($onInsert) {
                        $onInsert($fileInserted);
                    }
                }
                return $file;
            }, $files);
        } catch (\Exception) {
            return false;
        }

        return true;

    }

    /**
     * @param string $path
     * @param int $uploadToID
     * @param callable|null $onExist
     *
     * @return bool
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function doesFileOrDirectoryExistInDB (string $path, int $uploadToID, callable $onExist = null): bool
    {
        $currentFileRelPath = $this->helpers->getDriveSystemParentSignature(DriveConfig::getPrivatePath(), $path);
        $searchData = [
            'id'    => $uploadToID,
            'path'  => $currentFileRelPath,
            'query' => $this->helpers->getFileName($path),
        ];
        $filePath = $searchData['path'] . DIRECTORY_SEPARATOR . $searchData['query'];
        $searchResult = $this->searchFiles($searchData);
        $exist = false;
        if (isset($searchResult['data']) && is_array($searchResult['data'])) {
            foreach ($searchResult['data'] as $file) {
                if ($file->filepath === $filePath && $file->filename === $searchData['query']) {
                    $exist = true;
                    if ($onExist) {
                        $onExist($file);
                    }
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
    public function convertFilePathToFileObject (string $fullPath)
    {

        if ($this->helpers->fileExists($fullPath) === false) {
            return false;
        }

        $currentFileRelPath = str_replace(DriveConfig::getPrivatePath(), '', DriveConfig::getUploadsPath());

        $rootID = null;
        db(onGetDB: function ($db) use (&$rootID) {
            $table = $this->getDriveTable();
            $rootID = $db->row("Select `drive_id` FROM $table WHERE `drive_parent_id` IS NULL");
        });

        if (!isset($rootID->drive_id)) {
            return false;
        }
        $filename = $this->helpers->getFileName($fullPath);
        $rootID = $rootID->drive_id;
        $searchData = [
            'id'    => $rootID,
            'path'  => $currentFileRelPath,
            'query' => $filename,
        ];
        $searchResult = $this->searchFiles($searchData);
        $filePath = str_replace(DriveConfig::getPrivatePath(), '', $fullPath);
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
    protected function composeFile ($path, $uploadToID, $fileType)
    {
        $relPath = $this->helpers->getDriveSystemRelativeSignature(DriveConfig::getPrivatePath(), $path);
        $uniqueID = hash('sha256', $relPath . random_int(0000000, PHP_INT_MAX));

        $filename = $this->helpers->getFileName($path);

        $ext = ($fileType !== 'file') ? null : $this->helpers->extension($path);
        $properties = [
            'ext'           => $ext,
            'filename'      => $filename,
            'size'          => $this->helpers->fileSize($path),
            "time_created"  => $this->helpers->getFileTimeCreated($path),
            "time_modified" => $this->helpers->getFileTimeModified($path),
        ];
        $this->helpers->moreFileProperties($path, $ext, $properties);
        return [
            "drive_parent_id" => $uploadToID,
            "drive_unique_id" => $uniqueID,
            "type"            => $fileType,
            'filename'        => $filename,
            "properties"      => json_encode($properties),
            "security"        => json_encode([
                "lock"     => false,
                "password" => random_int(0000000, PHP_INT_MAX),
            ]),
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
     *
     * @param $blobInfo
     * @param $filePath
     *
     * @return object
     * @throws \Exception
     */
    public function isBlobChunkUploadCompleted ($blobInfo, $filePath): object
    {
        $blob_name = str_replace("{$this->getPath()}", '', $filePath);

        // Returns the count of rows that are either corrupted or needs to be filled
        // A data is corrupted if missing_blob_chunk_byte is greater than 0 (the missing byte is due to connection outage or some weird shit)
        // A data hasn't been filled if missing_blob_chunk_byte is null
        $totalUploadedChunks = null;
        $sum = null;
        db(onGetDB: function (TonicsQuery $db) use ($blob_name, $blobInfo, &$totalUploadedChunks, &$sum) {
            $totalUploadedChunks = $db
                ->run("SELECT count(*) as count FROM {$this->getBlobTable()} 
                            WHERE `blob_name` = ? AND missing_blob_chunk_byte = 0", $blob_name)[0];

            // The chunks that has been uploaded so far
            $totalUploadedChunks = $totalUploadedChunks->count;

            $sum = $db
                ->run("SELECT SUM(blob_chunk_size) as sum FROM {$this->getBlobTable()} 
                            WHERE `blob_name` = ? AND missing_blob_chunk_byte = 0", $blob_name)[0];
        });

        // The total no blob chunks that would be uploaded
        $totalChunks = (int)$blobInfo->totalChunks;


        return (object)[
            'totalUploadedChunks' => $totalUploadedChunks,
            'totalChunks'         => $totalChunks,
            'uploaded'            => (isset($sum->sum)) ? $this->helpers->formatBytes($sum->sum) : null,
            'uploadPercentage'    => ($totalUploadedChunks / $totalChunks) * 100,
            'isUploadCompleted'   => $totalUploadedChunks === $totalChunks,
        ];
    }

    /**
     * @param \stdClass $blobInfo
     * @param $data
     * @param $filePath
     *
     * @throws \Exception
     */
    public function insertBlobChunk (\stdClass $blobInfo, $data, $filePath)
    {
        $tbl = $this->getBlobTable();
        $outFile = $this->helpers->forceOpenFile($filePath);
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
            db(onGetDB: function ($db) use ($blobInfo, $data, $tbl) {
                $db->run("UPDATE $tbl SET `live_blob_chunk_size` = ?  WHERE `id` = ?;", strlen($data), $blobInfo->id);
            });
        }
    }

    /**
     * @param $totalChunks
     * @param $blob_name
     * @param int $chunksToDeleteAtATime
     *
     * @throws \Exception
     */
    public function deleteBlobs ($totalChunks, $blob_name, int $chunksToDeleteAtATime = 500): void
    {
        $noOfTimesToLoop = ceil($totalChunks / $chunksToDeleteAtATime);
        for ($i = 1; $i <= $noOfTimesToLoop; $i++) {
            $result = null;
            db(onGetDB: function ($db) use ($blob_name, $chunksToDeleteAtATime, &$result) {
                $db->run("DELETE FROM {$this->getBlobTable()} WHERE `blob_name` = ? ORDER BY `id` DESC LIMIT $chunksToDeleteAtATime", $blob_name);
            });
        }
    }

    /**
     * @throws \Exception
     */
    protected function getDriveIDFilesFromPathAndID ($path, $id): ?object
    {
        $result = null;
        db(onGetDB: function ($db) use ($id, $path, &$result) {
            $tableRows = $db->run("SELECT COUNT(*) AS 'r' FROM {$this->getDriveTable()} WHERE `drive_parent_id` = ?", $id)[0]->r;
            $result = $db->paginate(
                tableRows: $tableRows,
                callback: function ($perPage, $offset) use ($db, $id, $path) {
                    $columns = Tables::addColumnsToTable(
                        Tables::removeColumnFromTable(Tables::$DRIVE_SYSTEM_COLUMN, ['security', 'status']),
                        ["CONCAT(?, '/', filename) as filepath"], true);
                    $cbData = null;
                    db(onGetDB: function ($db) use ($offset, $perPage, $id, $path, $columns, &$cbData) {
                        $cbData = $db
                            ->run("SELECT $columns FROM {$this->getDriveTable()} WHERE drive_parent_id = ? ORDER BY 'drive_id' LIMIT ? OFFSET ?", $path, $id, $perPage, $offset);
                    });
                    return $cbData;
                }, perPage: url()->getParam('per_page', 50));
        });

        return $result;
    }

    /**
     * If file is not null you get array with items:
     *
     * - mainFile: the mainfile in object format
     * - fileSecurity: mainFile security settings in object format
     * - fullFilePath: The full file path location in string
     * - alias: alias path for nginx X-Accel
     *
     * @param string $uniqueID
     *
     * @return array|null
     * @throws \Exception
     */
    public function getInfoOfUniqueID (string $uniqueID): ?array
    {
        #
        # RECURSIVELY GET THE PARENT OF THE FILE,
        # I HAVE RESTRICTED THIS TO WORK ONLY FOR FILE, MEANING, YOU CAN ONLY DOWNLOAD A FILE AND NOT A FOLDER
        #
        $fileInfo = null;
        db(onGetDB: function ($db) use ($uniqueID, &$fileInfo) {
            $fileInfo = $db->run("WITH RECURSIVE child_to_parent AS (
  SELECT drive_id, drive_parent_id, drive_unique_id, filename, CAST(filename AS VARCHAR (255)) AS filepath, status, properties, security
  FROM {$this->getDriveTable()} WHERE drive_unique_id = ? AND type = 'file' UNION ALL 
  SELECT  f.drive_id, f.drive_parent_id, f.drive_unique_id, f.filename, CONCAT(f.filename, '/', filepath),  f.status, f.properties, f.security 
  FROM {$this->getDriveTable()} AS f INNER JOIN child_to_parent cp on f.drive_id = cp.drive_parent_id
) SELECT * FROM child_to_parent;", $uniqueID);
        });

        if (!is_array($fileInfo) || empty($fileInfo)) {
            return null;
        }

        $mainFile = $fileInfo[0];
        $fileSecurity = json_decode($mainFile->security);
        $path = $fileInfo[array_key_last($fileInfo)];
        $fullFilePath = DriveConfig::getPrivatePath() . '/' . $path->filepath;
        $aliasPath = DriveConfig::xAccelDownloadFilePath() . $path->filepath;

        return [
            'mainFile'     => $mainFile,
            'fileSecurity' => $fileSecurity,
            'fullFilePath' => $fullFilePath,
            'aliasPath'    => $aliasPath,
        ];

    }

    /**
     * Get the ID of the last element in the $pathTrail,
     * Note this only works for Directory
     *
     * @param array $pathTrail
     * Path Trail of the folder e.g. path trail of folder /var/www/downloads/music is ['uploads', 'www', 'downloads', 'music']
     * @param int|null $parentID
     * Where To Start The Recursion, This should usually be null meaning the root folder
     * @param int $maxDepth
     * Max Recursion Depth Limit Before Quiting
     *
     * @return bool|int
     * @throws \Exception
     */
    public function findChildRealID (array $pathTrail, int $parentID = null, int $maxDepth = 10000): bool|int
    {

        $numberOfQ = $this->getQuestionMarks(sizeof($pathTrail));

        # Note: Ordering by the `drive_parent_id` gives us higher probability to find the childID quickly, it's a quick optimization but not necessary at all.
        $pathTrailRows = null;
        db(onGetDB: function ($db) use ($numberOfQ, $pathTrail, &$pathTrailRows) {
            $pathTrailRows = $db->run(<<<SQL
SELECT drive_id, drive_parent_id, filename  FROM {$this->getDriveTable()} 
WHERE type = 'directory' AND filename IN($numberOfQ) ORDER BY drive_parent_id
SQL, ...$pathTrail);
        });

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
    private function findChildInPathTrail (array $pathTrailRows, $noOfPath, int $parentID = null, $maxDepth = 10000, &$child = [], $depth = 0): array
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


    public function reverseWords ($str, $separator = '/'): string
    {
        return implode($separator, array_reverse(explode($separator, $str)));
    }

    /**
     * Returns question marks, e.g, if you pass 3, it returns "?,?,?"
     *
     * @param int $marks
     *
     * @return string
     */
    public function getQuestionMarks (int $marks): string
    {
        return implode(',', array_fill(0, $marks, '?'));
    }

    public function getBlobTable (): string
    {
        return Tables::getTable(Tables::DRIVE_BLOB_COLLATOR);
    }

    public function getDriveTable (): string
    {
        return Tables::getTable(Tables::DRIVE_SYSTEM);
    }

    /**
     * @return string
     */
    public function getPath (): string
    {
        return $this->path;
    }
}