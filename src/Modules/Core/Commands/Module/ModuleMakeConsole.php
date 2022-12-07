<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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