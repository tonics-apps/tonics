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

namespace App\Modules\Core\Commands\Module;

use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO CREATE A CONSOLE BOILER-PATE,  RUN: php bin/console --module=Core --make:console=console_name
 *
 * Class ModuleMakeConsole
 * @package App\Commands\Module
 */
class ModuleMakeConsole implements ConsoleCommand
{

use ConsoleColor;

    public function required(): array
    {
        return  [
            "--module",
            "--make:console"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $s = DIRECTORY_SEPARATOR; $module = $commandOptions['--module'];
        if ($moduleDir = helper()->findModuleDirectory($commandOptions['--module'])) {
            $consoleTemplate = APP_ROOT . "{$s}src{$s}Modules{$s}Core{$s}Commands{$s}Module{$s}Template{$s}ConsoleExample.txt";
            if (helper()->copyConsoleTemplate(
                $consoleTemplate,
                consoleName: $commandOptions['--make:console'],
                moduleDir: $moduleDir,
                moduleName: $module)) {
                $this->successMessage("Commands Template Successfully Created Under '$module' Commands Directory ");
            } else {
                $this->errorMessage("Couldn't create a Commands Template");
            }
        }
    }
}