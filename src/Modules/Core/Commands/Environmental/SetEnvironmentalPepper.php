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

namespace App\Modules\Core\Commands\Environmental;


use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO Create Pepper, RUN: php bin/console --set:environmental --pepper
 *
 * Class SetEnvironmentalPepper
 * @package App\Commands\Environmental
 */
class SetEnvironmentalPepper extends EnvironmentalAbstract implements ConsoleCommand
{

    public function required(): array
    {
        return [
            "--set:environmental",
            "--pepper"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        if ($this->setEnvironmentValue('PEPPER', helper()->randString())){
            $this->successMessage("PEPPER Added");
        } else {
            $this->errorMessage("Failed To Add Environmental PEPPER");
            exit();
        }
    }
}