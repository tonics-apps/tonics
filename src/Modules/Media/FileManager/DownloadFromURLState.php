<?php

namespace App\Modules\Media\FileManager;

use App\Configs\AppConfig;
use App\Configs\DriveConfig;
use App\Library\SimpleState;
use App\Modules\Media\FileManager\Exceptions\FileException;

class DownloadFromURLState extends SimpleState
{
    private string $url;
    private string $uploadTo;
    private string $filename;
    private array $headers;
    private bool $urlIsValid;
    private array $preflightData = [];
    // 4194304
    private int $bytePerChunk = 4194304; // 4mb
    private ?int $parentDriveID = null;

    # States For ExtractFileState
    const InitialState = 'InitialState';
    const ResolveFilenameState = 'ResolveFilenameState';
    const FilenameFromContentDispositionState = 'FilenameFromContentDispositionState';
    const FilenameFromURLState = 'FilenameFromURLState';
    const PrepareFileDownloadState = 'PrepareFileDownloadState';
    const FilePreflightState = 'FilePreflightState';
    const DownloadNonResuming = 'DownloadNonResuming';
    const DownloadResuming = 'DownloadResuming';
    const DownloadCompleted = 'DownloadCompleted';

    static array $STATES = [
        self::InitialState => self::InitialState,
    ];

    private LocalDriver $localDriver;
    private float $totalChunks;

    /**
     * @throws \Exception
     */
    public function __construct(LocalDriver $localDriver, string $url, string $uploadTo, string $filename)
    {
        $this->localDriver = $localDriver;
        $this->url = $url;
        $this->uploadTo = $uploadTo;
        $this->filename = $localDriver->normalizeFileName($filename);

        if (empty($uploadTo)) {
            $this->uploadTo = DriveConfig::getUploadsPath();
        } elseif (!helper()->fileExists(DriveConfig::getUploadsPath() . DIRECTORY_SEPARATOR . $uploadTo)) {
            $this->uploadTo = DriveConfig::getUploadsPath();
        }

        $dirPath = str_replace(DriveConfig::getPrivatePath(), '', $this->uploadTo);
        $pathID = $this->getLocalDriver()->findChildRealID(array_filter(explode(DIRECTORY_SEPARATOR, $dirPath)));
        if ($pathID === false){
            throw new \Exception("Couldn't Find The UploadTo Drive ID in Db");
        }
        $this->parentDriveID = $pathID;
        $headers = helper()->getHeadersFromURL($url);
        if (helper()->remoteFileExists($url, $headers)) {
            $this->headers = $headers;
            $this->urlIsValid = true;
        } else {
            $this->urlIsValid = false;
        }
    }

    /**
     * @throws \Exception
     */
    public function InitialState(): string
    {
        if ($this->urlIsValid === false) {
            return self::ERROR;
        }

        $this->switchState(self::ResolveFilenameState);
        return self::NEXT;
    }

    public function ResolveFilenameState(): string
    {
        if (empty($this->filename)) {
            $this->switchState(self::FilenameFromContentDispositionState);
            return self::NEXT;
        }
        $this->switchState(self::PrepareFileDownloadState);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function FilenameFromContentDispositionState(): string
    {
        $this->filename = trim(helper()->getFilenameFromContentDisposition($this->headers));
        if (empty($this->filename)) {
            $this->switchState(self::FilenameFromURLState);
            return self::NEXT;
        }
        $this->switchState(self::PrepareFileDownloadState);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function FilenameFromURLState(): string
    {
        $path = parse_url($this->url, PHP_URL_PATH);
        $path = trim($path, '/');
        $pathExploded = explode('/', $path); // get delimited by a slash
        $this->filename = helper()->normalizeFileName(end($pathExploded), '_');

        $this->switchState(self::PrepareFileDownloadState);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function PrepareFileDownloadState(): string
    {
        helper()->addEventStreamHeader();
        if (isset($this->headers['Content-Length']) && isset($this->headers['Accept-Ranges']) && $this->headers['Accept-Ranges'] === 'bytes') {
            $this->switchState(self::FilePreflightState);
            return self::NEXT;
        }

        $this->switchState(self::DownloadNonResuming);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function FilePreflightState(): string
    {
        $bytePerChunk = $this->bytePerChunk;
        $totalByteSize = (int)$this->headers['Content-Length'];
        $blob_path = str_replace(DriveConfig::getPrivatePath(), '', $this->uploadTo);
        $data = [
            'Uploadto' => $blob_path,
            'Filename' => $this->filename,
            'Byteperchunk' => $bytePerChunk,
            'Totalblobsize' => $totalByteSize,
            'Chunkstosend' => ceil($totalByteSize / ($bytePerChunk))
        ];
        $preflightData = $this->getLocalDriver()->preFlight($data);
        $ranges = $this->getByteRanges($preflightData['preflightData']);
        $preflightData['ranges'] = $ranges;
        $this->preflightData = $preflightData;
        $this->totalChunks = ceil($totalByteSize / ($bytePerChunk));

        $this->switchState(self::DownloadResuming);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function DownloadResuming(): string
    {
        $filePath = $this->uploadTo . DIRECTORY_SEPARATOR . $this->filename;
        $curl = curl_init($this->url);
        $uploaded = 0;
        $currentBlobID = 0;
        $blobData = null;

        curl_setopt_array($curl, [
            CURLOPT_CONNECTTIMEOUT => 0, // 0 means forever
            CURLOPT_RANGE => $this->preflightData['ranges'],
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_WRITEFUNCTION => function ($curl, $chunkData) use ($filePath, &$blobData, &$currentBlobID, &$uploaded) {
                $len = strlen($chunkData);
                $blobData .= $chunkData;
                if ($uploaded >= $this->bytePerChunk) {
                    $uploaded = 0;
                    $blobInfo = json_decode($this->preflightData['preflightData'][$currentBlobID]->moreBlobInfo);
                    $blobInfo->id = $this->preflightData['preflightData'][$currentBlobID]->id;
                    $subBlob = substr($blobData, 0,  $this->bytePerChunk);
                    $blobData = substr($blobData, $this->bytePerChunk, strlen($blobData));
                    $this->getLocalDriver()->insertBlobChunk($blobInfo, $subBlob, $filePath);
                    helper()->sendMsg(self::getCurrentState(), $this->chunkProgress($filePath, true));
                    ++$currentBlobID;
                }
                $uploaded = $uploaded + $len;
                return $len;
            }
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === true) {
            $blobInfo = json_decode($this->preflightData['preflightData'][$currentBlobID]->moreBlobInfo);
            $blobInfo->id = $this->preflightData['preflightData'][$currentBlobID]->id;
            $this->getLocalDriver()->insertBlobChunk($blobInfo, $blobData, $filePath);
            helper()->sendMsg(self::getCurrentState(), $this->chunkProgress($filePath, true));
            $this->getLocalDriver()->deleteBlobs($this->totalChunks, $this->preflightData['preflightData'][$currentBlobID]->blob_name);
            $this->switchState(self::DownloadCompleted);
            $this->insertFileToDB($filePath);
           return self::NEXT;
        }
        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function DownloadNonResuming(): string
    {
        $filePath = $this->uploadTo . DIRECTORY_SEPARATOR . $this->filename;
        $curl = curl_init($this->url);
        $uploaded = 0;
        $blobData = null;
        $uploadedProgress = 0;

        $outFile = helper()->forceOpenFile($filePath);

        if ($outFile === false){
            return self::ERROR;
        }

        curl_setopt_array($curl, [
            CURLOPT_CONNECTTIMEOUT => 0, // 0 means forever
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_WRITEFUNCTION => function ($curl, $chunkData) use (&$uploadedProgress, $outFile, $filePath, &$blobData, &$uploaded) {
                $len = strlen($chunkData);
                $blobData .= $chunkData;
                if ($uploaded >= $this->bytePerChunk) {
                    $uploaded = 0;
                    $subBlob = substr($blobData, 0,  $this->bytePerChunk);
                    $blobData = substr($blobData, $this->bytePerChunk, strlen($blobData));
                    fwrite($outFile, $subBlob);
                    helper()->sendMsg(self::getCurrentState(), $this->chunkProgress($filePath, false, $uploadedProgress));
                }
                $uploaded = $uploaded + $len;
                $uploadedProgress = $uploadedProgress + $len;
                return $len;
            }
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === true) {
            fwrite($outFile, $blobData);
            helper()->sendMsg(self::getCurrentState(), $this->chunkProgress($filePath, false, $uploadedProgress, true));
            $this->switchState(self::DownloadCompleted);
            fclose($outFile);
            $outFile = null;
            $this->insertFileToDB($filePath);
            return self::NEXT;
        }
        fclose($outFile);
        $outFile = null;
        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function DownloadCompleted(): string
    {
        helper()->sendMsg(self::getCurrentState(), "> Close", 'close', retry: 10000000000000000000000);
        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    private function insertFileToDB(string $filePath)
    {
        $currentFileRelPath = helper()->getDriveSystemParentSignature(DriveConfig::getPrivatePath(), $filePath);
        $searchData = [
            'id' =>  $this->parentDriveID,
            'path' => $currentFileRelPath,
            'query' => $this->filename
        ];
        $searchResult = $this->getLocalDriver()->searchFiles($searchData);
        if ($searchResult === false || empty($searchResult['data'])){
            $this->getLocalDriver()->insertFileToDB($filePath, $this->parentDriveID);
        }

    }


    /**
     * @param string $filePath
     * @param bool $canResume
     * @param int|null $byteUploaded
     * Fill this when upload can't resume, meaning when $canResume is false
     * @param bool $completed
     * Only applies when $canResume is false
     * @return object
     * @throws \Exception
     */
    private function chunkProgress(string $filePath, bool $canResume = false, int $byteUploaded = null, bool $completed = false): object
    {
        if ($canResume){
            $blobProgressInfo = $this->getLocalDriver()->isBlobChunkUploadCompleted((object)['totalChunks' => $this->totalChunks], $filePath);
            $blobProgressInfo->canResume = $canResume;
            $blobProgressInfo->totalSize = helper()->formatBytes((int)$this->headers['Content-Length']);
        } else {
            $blobProgressInfo = (object)[
                'canResume' => $canResume,
                'uploaded' => helper()->formatBytes($byteUploaded),
                'isUploadCompleted' => $completed,
            ];
        }

        return $blobProgressInfo;
    }

    /**
     * @return LocalDriver
     */
    public function getLocalDriver(): LocalDriver
    {
        return $this->localDriver;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @throws \Exception
     */
    private function issue($id, $msg, $event = 'issue', bool $closeStream = true)
    {
        helper()->sendMsg($id, "> $msg", $event, 900000000000000000);
        if ($closeStream) {
            helper()->sendMsg($id, "> Close", 'close');
        }
    }

    /**
     * @param array $preflightData
     * @param bool $multiRange
     * @return string
     */
    private function getByteRanges(array $preflightData, bool $multiRange = false): string
    {
        $ranges = '';
        $count = count($preflightData) - 1;
        foreach ($preflightData as $k => $data) {
            $blobInfo = json_decode($data->moreBlobInfo);
            $start = $blobInfo->startSlice;
            $end = $blobInfo->endSlice;
            if ($multiRange === false){
                return "$start-";
            }
            if ($count === $k) {
                $ranges .= "$start-$end";
            } else {
                $ranges .= "$start-$end,";
            }
        }

        return $ranges;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getUploadTo(): string
    {
        return $this->uploadTo;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}