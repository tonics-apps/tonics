<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Commands\Module;

use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO CREATE A CONSOLE BOILER-PATE,  RUN: php bin/console --module=core --make:console=console_name
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
        $s = DIRECTORY_SEPARATOR; $module = ucfirst(strtolower($commandOptions['--module']));
        if ($moduleDir = helper()->findModuleDirectory($commandOptions['--module'])) {
            $consoleTemplate = APP_ROOT . "{$s}src{$s}Commands{$s}Module{$s}Template{$s}ConsoleExample.txt";
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