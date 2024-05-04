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