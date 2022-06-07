<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Commands\Module;


use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsContainer\Container;

/**
 * TO MIGRATE A SPECIFIC MODULE DOWN RUN: php bin/console --module:migrate=core --down
 *
 * Class ModuleMigrateDown
 * @package App\Commands\Module
 */
class ModuleMigrateDown extends DatabaseMigrationAbstract implements ConsoleCommand
{

    public function required(): array
    {
        return [
            "--module:migrate",
            "--down"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {

        $s = DIRECTORY_SEPARATOR; $module = ucfirst(strtolower($commandOptions['--module:migrate']));
        $moduleDir = helper()->findModuleDirectory($module) . "{$s}Database{$s}Migrations";
        if ($migrationFiles = helper()->findFilesWithExtension(['php'], $moduleDir)) {
            foreach ($migrationFiles as $migrationFile) {
                $class = helper()->getFullClassName(file_get_contents($migrationFile));
                # This would reference the dbName in the migration table
                $dbMigrationName = strtolower(helper()->basePath($migrationFile));
                $this->handleMigrateDown($class, $dbMigrationName);
            }
        } else {
            $this->errorMessage("Nothing To Migrate in '{$commandOptions['--module:migrate']}' Directory");
        }
    }
}