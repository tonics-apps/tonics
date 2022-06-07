<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Commands\Module;

use App\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO CREATE A MIGRATION BOILER-PATE,  RUN: `php bin/console --module=page --make:migration=migration_name`
 *
 * Class ModuleMakeMigration
 * @package App\Commands\Module
 */
class ModuleMakeMigration implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--module",
            "--make:migration"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $s = DIRECTORY_SEPARATOR; $module = ucfirst(strtolower($commandOptions['--module']));
        if ($moduleDir = helper()->findModuleDirectory($module)) {
            $migrationTemplate = APP_ROOT . "{$s}src{$s}Commands{$s}Module{$s}Template{$s}MigrationExample.txt";
            if ($copiedFile = helper()->copyMigrationTemplate(
                $migrationTemplate,
                migrationName: $commandOptions['--make:migration'],
                moduleDir: $moduleDir,
                moduleName: $module)) {
                $this->successMessage("Migration Successfully Created Under '$module' Migration Directory With Name $copiedFile");
            } else {
                $this->errorMessage("Couldn't Create Migration");
            }
        }
    }
}