<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Events\TonicsTemplateViewEvent;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class BeforeCombineModeOperation implements EventInterface
{

    private string $outputFile;
    private bool $combineFiles = true;
    private string $rootPath;

    public function __construct(string $outputName, string $rootPath)
    {
        $this->outputFile = $outputName;
        $this->rootPath = $rootPath;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getOutputFile(): string
    {
        return $this->outputFile;
    }

    /**
     * @param string $outputFile
     */
    public function setOutputFile(string $outputFile): void
    {
        $this->outputFile = $outputFile;
    }

    /**
     * @return bool
     */
    public function combineFiles(): bool
    {
        return $this->combineFiles;
    }

    /**
     * @param bool $combineFiles
     */
    public function setCombineFiles(bool $combineFiles): void
    {
        $this->combineFiles = $combineFiles;
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * @param string $rootPath
     */
    public function setRootPath(string $rootPath): void
    {
        $this->rootPath = $rootPath;
    }
}