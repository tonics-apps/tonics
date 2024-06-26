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

namespace App\Modules\Media\States;

use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Media\FileManager\LocalDriver;

class DownloadFromURLState extends SimpleState
{
    const InitialState                        = 'InitialState';
    const ResolveFilenameState                = 'ResolveFilenameState';
    const FilenameFromContentDispositionState = 'FilenameFromContentDispositionState';
    const FilenameFromURLState                = 'FilenameFromURLState';
    const PrepareFileDownloadState            = 'PrepareFileDownloadState';
    const FilePreflightState                  = 'FilePreflightState';
    const DownloadNonResuming                 = 'DownloadNonResuming';
    // 4194304
    const DownloadResuming  = 'DownloadResuming'; // 4mb
    const DownloadCompleted = 'DownloadCompleted';

    # States For ExtractFileState
    static array        $STATES        = [
        self::InitialState => self::InitialState,
    ];
    private string      $url;
    private string      $uploadTo;
    private string      $filename;
    private array       $headers;
    private bool        $urlIsValid;
    private bool        $importToDB    = true;
    private array       $preflightData = [];
    private int         $bytePerChunk  = 4194304;
    private ?int        $parentDriveID = null;
    private LocalDriver $localDriver;
    private float       $totalChunks;

    private bool $messageDebug = true;

    /**
     * @throws \Exception
     */
    public function __construct (LocalDriver $localDriver, string $url, string $uploadTo, string $filename, bool $importToDB = true)
    {
        $this->localDriver = $localDriver;
        $this->url = $url;
        $this->importToDB = $importToDB;
        $this->uploadTo = $uploadTo;
        $this->filename = $localDriver->normalizeFileName($filename);

        if (empty($uploadTo)) {
            $this->uploadTo = DriveConfig::getUploadsPath();
        }

        if ($this->importToDB) {
            $dirPath = str_replace(DriveConfig::getPrivatePath(), '', $this->uploadTo);
            $pathID = $this->getLocalDriver()->findChildRealID(array_filter(explode(DIRECTORY_SEPARATOR, $dirPath)));
            if ($pathID === false) {
                throw new \Exception("Couldn't Find The UploadTo Drive ID in Db");
            }
            $this->parentDriveID = $pathID;
        }

        $headers = helper()->getHeadersFromURL($url);
        if (is_array($headers) && helper()->remoteFileExists($url, $headers)) {
            $this->headers = $headers;
            $this->urlIsValid = true;
        } else {
            $this->urlIsValid = false;
        }
    }

    /**
     * @throws \Exception
     */
    public function InitialState (): string
    {
        if ($this->urlIsValid === false) {
            return self::ERROR;
        }

        $this->switchState(self::ResolveFilenameState);
        return self::NEXT;
    }

    public function ResolveFilenameState (): string
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
    public function FilenameFromContentDispositionState (): string
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
    public function FilenameFromURLState (): string
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
    public function PrepareFileDownloadState (): string
    {
        if (isset($this->headers['Content-Length']) && !is_array($this->headers['Content-Length']) && isset($this->headers['Accept-Ranges']) && $this->headers['Accept-Ranges'] === 'bytes') {
            $this->switchState(self::FilePreflightState);
            return self::NEXT;
        }

        $this->switchState(self::DownloadNonResuming);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function FilePreflightState (): string
    {
        $bytePerChunk = $this->bytePerChunk;
        $totalByteSize = (int)$this->headers['Content-Length'];
        $blob_path = str_replace(DriveConfig::getPrivatePath(), '', $this->uploadTo);
        $data = [
            'Uploadto'      => $blob_path,
            'Filename'      => $this->filename,
            'Byteperchunk'  => $bytePerChunk,
            'Totalblobsize' => $totalByteSize,
            'Chunkstosend'  => ceil($totalByteSize / ($bytePerChunk)),
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
    public function DownloadResuming (): string
    {
        $filePath = $this->uploadTo . DIRECTORY_SEPARATOR . $this->filename;
        $curl = curl_init($this->url);
        $uploaded = 0;
        $currentBlobID = 0;
        $blobData = null;

        curl_setopt_array($curl, [
            CURLOPT_CONNECTTIMEOUT       => 0, // 0 means forever
            CURLOPT_RANGE                => $this->preflightData['ranges'],
            CURLOPT_CUSTOMREQUEST        => 'GET',
            CURLOPT_SSL_VERIFYHOST       => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION       => true,
            CURLOPT_COOKIEFILE           => '',
            CURLOPT_RETURNTRANSFER       => true,
            CURLOPT_USERAGENT            => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
            CURLOPT_WRITEFUNCTION        => function ($curl, $chunkData) use ($filePath, &$blobData, &$currentBlobID, &$uploaded) {
                $len = strlen($chunkData);
                $blobData .= $chunkData;
                if ($uploaded >= $this->bytePerChunk) {
                    $uploaded = 0;
                    $blobInfo = json_decode($this->preflightData['preflightData'][$currentBlobID]->moreBlobInfo);
                    $blobInfo->id = $this->preflightData['preflightData'][$currentBlobID]->id;
                    $subBlob = substr($blobData, 0, $this->bytePerChunk);
                    $blobData = substr($blobData, $this->bytePerChunk, strlen($blobData));
                    $this->getLocalDriver()->insertBlobChunk($blobInfo, $subBlob, $filePath);
                    $percentage = $this->chunkProgress($filePath, true)->uploadPercentage;
                    if ($this->isMessageDebug()) {
                        helper()->sendMsg(self::getCurrentState(), 'Percentage: ' . $percentage);
                    }

                    ++$currentBlobID;
                }
                $uploaded = $uploaded + $len;
                return $len;
            },
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === true) {
            $blobInfo = json_decode($this->preflightData['preflightData'][$currentBlobID]->moreBlobInfo);
            $blobInfo->id = $this->preflightData['preflightData'][$currentBlobID]->id;
            $this->getLocalDriver()->insertBlobChunk($blobInfo, $blobData, $filePath);
            $percentage = $this->chunkProgress($filePath, true)->uploadPercentage;

            if ($this->isMessageDebug()) {
                helper()->sendMsg(self::getCurrentState(), 'Percentage: ' . $percentage);
            }

            $this->getLocalDriver()->deleteBlobs($this->totalChunks, $this->preflightData['preflightData'][$currentBlobID]->blob_name);
            $this->switchState(self::DownloadCompleted);
            if ($this->importToDB) {
                $this->insertFileToDB($filePath);
            }
            return self::NEXT;
        }
        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function DownloadNonResuming (): string
    {
        $filePath = $this->uploadTo . DIRECTORY_SEPARATOR . $this->filename;
        $curl = curl_init($this->url);
        $uploaded = 0;
        $blobData = null;
        $uploadedProgress = 0;

        $outFile = helper()->forceOpenFile($filePath);

        if ($outFile === false) {
            return self::ERROR;
        }
        
        curl_setopt_array($curl, [
            CURLOPT_CONNECTTIMEOUT       => 0, // 0 means forever
            CURLOPT_CUSTOMREQUEST        => 'GET',
            CURLOPT_SSL_VERIFYHOST       => false,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_FOLLOWLOCATION       => true,
            CURLOPT_RETURNTRANSFER       => true,
            CURLOPT_COOKIEFILE           => '',
            CURLOPT_USERAGENT            => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36',
            CURLOPT_WRITEFUNCTION        => function ($curl, $chunkData) use (&$uploadedProgress, $outFile, $filePath, &$blobData, &$uploaded) {
                $len = strlen($chunkData);
                $blobData .= $chunkData;
                if ($uploaded >= $this->bytePerChunk) {
                    $uploaded = 0;
                    $subBlob = substr($blobData, 0, $this->bytePerChunk);
                    $blobData = substr($blobData, $this->bytePerChunk, strlen($blobData));
                    fwrite($outFile, $subBlob);
                    $fileUploaded = $this->chunkProgress($filePath, false, $uploadedProgress)->uploaded;

                    if ($this->isMessageDebug()) {
                        helper()->sendMsg(self::getCurrentState(), "Uploaded: $fileUploaded");
                    }
                }
                $uploaded = $uploaded + $len;
                $uploadedProgress = $uploadedProgress + $len;
                return $len;
            },
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === true) {
            fwrite($outFile, $blobData);
            $fileUploaded = $this->chunkProgress($filePath, false, $uploadedProgress, true)->uploaded;

            if ($this->isMessageDebug()) {
                helper()->sendMsg(self::getCurrentState(), "Uploaded: $fileUploaded");
            }

            $this->switchState(self::DownloadCompleted);
            fclose($outFile);
            $outFile = null;
            if ($this->importToDB) {
                $this->insertFileToDB($filePath);
            }
            return self::NEXT;
        }
        fclose($outFile);
        $outFile = null;
        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function DownloadCompleted (): string
    {
        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    private function insertFileToDB (string $filePath)
    {
        $currentFileRelPath = helper()->getDriveSystemParentSignature(DriveConfig::getPrivatePath(), $filePath);
        $searchData = [
            'id'    => $this->parentDriveID,
            'path'  => $currentFileRelPath,
            'query' => $this->filename,
        ];
        $searchResult = $this->getLocalDriver()->searchFiles($searchData);
        if ($searchResult === false || empty($searchResult['data'])) {
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
     *
     * @return object
     * @throws \Exception
     */
    private function chunkProgress (string $filePath, bool $canResume = false, int $byteUploaded = null, bool $completed = false): object
    {
        if ($canResume) {
            $blobProgressInfo = $this->getLocalDriver()->isBlobChunkUploadCompleted((object)['totalChunks' => $this->totalChunks], $filePath);
            $blobProgressInfo->canResume = $canResume;
            $blobProgressInfo->totalSize = helper()->formatBytes((int)$this->headers['Content-Length']);
        } else {
            $blobProgressInfo = (object)[
                'canResume'         => $canResume,
                'uploaded'          => helper()->formatBytes($byteUploaded),
                'isUploadCompleted' => $completed,
            ];
        }

        return $blobProgressInfo;
    }

    /**
     * @return LocalDriver
     */
    public function getLocalDriver (): LocalDriver
    {
        return $this->localDriver;
    }

    /**
     * @return array
     */
    public function getHeaders (): array
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function isMessageDebug (): bool
    {
        return $this->messageDebug;
    }

    /**
     * @param bool $messageDebug
     */
    public function setMessageDebug (bool $messageDebug): void
    {
        $this->messageDebug = $messageDebug;
    }

    /**
     * @throws \Exception
     */
    private function issue ($id, $msg, $event = 'issue', bool $closeStream = true)
    {
        helper()->sendMsg($id, "> $msg", $event, 900000000000000000);
        if ($closeStream) {
            helper()->sendMsg($id, "> Close", 'close');
        }
    }

    /**
     * @param array $preflightData
     * @param bool $multiRange
     *
     * @return string
     */
    private function getByteRanges (array $preflightData, bool $multiRange = false): string
    {
        $ranges = '';
        $count = count($preflightData) - 1;
        foreach ($preflightData as $k => $data) {
            $blobInfo = json_decode($data->moreBlobInfo);
            $start = $blobInfo->startSlice;
            $end = $blobInfo->endSlice;
            if ($multiRange === false) {
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
    public function getUrl (): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getUploadTo (): string
    {
        return $this->uploadTo;
    }

    /**
     * @return string
     */
    public function getFilename (): string
    {
        return $this->filename;
    }
}