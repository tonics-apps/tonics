<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Media\States;

use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Media\FileManager\LocalDriver;

class ExtractFileState extends SimpleState
{

    private LocalDriver $localDriver;
    private string $extractedFilePath = '';


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
        $result = $this->localDriver->recursivelyCreateFileOrDirectory($this->extractedFilePath);
        return ($result === true) ? self::DONE: self::ERROR;
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