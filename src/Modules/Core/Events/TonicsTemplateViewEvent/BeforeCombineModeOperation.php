<?php

namespace App\Modules\Core\Events\TonicsTemplateViewEvent;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class BeforeCombineModeOperation implements EventInterface
{

    private string $outputFile;
    private bool $combineFiles = true;

    public function __construct(string $outputName)
    {
        $this->outputFile = $outputName;
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
}