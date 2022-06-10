<?php

namespace App\Modules\Media\FileManager;

use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\SimpleState;

class ExtractFileState extends SimpleState
{

    private LocalDriver $localDriver;
    private string $extractedFilePath = '';
    private array $handledPath = [];
    private ?\stdClass $currentParent = null;


    # States For ExtractFileState
    const ExtractFileStateInitial = 'ExtractFileStateInitial';

    public function __construct(LocalDriver $localDriver){
        $this->localDriver = $localDriver;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function ExtractFileStateInitial(): string
    {
        $files = explode(DIRECTORY_SEPARATOR, str_replace(DriveConfig::getPrivatePath(), '', $this->extractedFilePath));
        if (empty($files)){
            return self::ERROR;
        }

        $files = array_filter($files);
        $currentFileRelPath = '';
        try {
            array_map(function ($file) use (&$currentFileRelPath) {
                $currentFileRelPath .= DIRECTORY_SEPARATOR . $file;

                if (key_exists($currentFileRelPath, $this->handledPath)){
                    $this->currentParent = $this->handledPath[$currentFileRelPath];
                }

                if (helper()->isDirectory(DriveConfig::getPrivatePath() . $currentFileRelPath)){
                    if (!key_exists($currentFileRelPath, $this->handledPath)){
                        $pathID = $this->getLocalDriver()->findChildRealID(array_filter(explode(DIRECTORY_SEPARATOR, $currentFileRelPath)));
                        $this->insertDirectory($pathID, $currentFileRelPath);
                    }
                }

                if (helper()->isFile(DriveConfig::getPrivatePath() . $currentFileRelPath)){
                    $exist = $this->getLocalDriver()->doesFileOrDirectoryExistInDB(DriveConfig::getPrivatePath() . $currentFileRelPath, $this->currentParent->drive_id);
                    if ($exist === false){
                        $this->insertFile($currentFileRelPath);
                    }
                }
                return $file;
            }, $files);
        }catch (\Exception){
            return self::ERROR;
        }

        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function insertDirectory($pathID, $currentFileRelPath)
    {
        ## Meaning No Such Path Exist
        if ($pathID === false){
            $data = $this->getLocalDriver()
                ->insertFileToDBReturning(
                    DriveConfig::getPrivatePath() . $currentFileRelPath,
                    $this->currentParent->drive_id,
                    'directory', ['drive_id']);
        } else {
            $data = db()->row(<<<SQL
SELECT * FROM {$this->getLocalDriver()->getDriveTable()} WHERE `drive_id` = ?
SQL, $pathID);
        }
        $this->handledPath[$currentFileRelPath] = $data;
        $this->currentParent = $data;
    }

    /**
     * @throws \Exception
     */
    public function insertFile($currentFileRelPath)
    {
        $this->getLocalDriver()->insertFileToDB(DriveConfig::getPrivatePath() .$currentFileRelPath, $this->currentParent->drive_id);
    }

    /**
     * @return LocalDriver
     */
    public function getLocalDriver(): LocalDriver
    {
        return $this->localDriver;
    }

    /**
     * @return string
     */
    public function getExtractedFilePath(): string
    {
        return $this->extractedFilePath;
    }

    /**
     * @param string $extractedFilePath
     * @return ExtractFileState
     */
    public function setExtractedFilePath(string $extractedFilePath): ExtractFileState
    {
        $this->extractedFilePath = $extractedFilePath;
        return $this;
    }

}