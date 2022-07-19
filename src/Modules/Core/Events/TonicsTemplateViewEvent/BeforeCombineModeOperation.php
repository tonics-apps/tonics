<?php

namespace App\Modules\Core\Events\TonicsTemplateViewEvent;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class BeforeCombineModeOperation implements EventInterface
{

    private string $outputName;
    private bool $combineFiles = true;

    public function __construct(string $outputName)
    {
        $this->outputName = $outputName;
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
    public function getOutputName(): string
    {
        return $this->outputName;
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