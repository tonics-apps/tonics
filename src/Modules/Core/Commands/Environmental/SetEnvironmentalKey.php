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
 * TO Create Encryption Key, RUN: php bin/console --set:environmental --key
 *
 * Class SetEnvironmentalKey
 * @package App\Commands\Environmental
 */
class SetEnvironmentalKey extends EnvironmentalAbstract implements ConsoleCommand
{

    public function required (): array
    {
        return [
            "--set:environmental",
            "--key",
        ];
    }

    /**
     * @param array $commandOptions
     *
     * @throws \Exception
     */
    public function run (array $commandOptions): void
    {
        # Update key would aid developer to identify premium users if developers are providing premium extensions
        if ($this->setEnvironmentValue('APP_KEY', helper()->randString()) && $this->setEnvironmentValue('SITE_KEY', helper()->randString())) {
            $this->successMessage("App Key Added");
        } else {
            $this->errorMessage("Failed To Add Environmental Key");
            exit();
        }
    }
}