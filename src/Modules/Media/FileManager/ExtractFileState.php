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

use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\SimpleState;

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