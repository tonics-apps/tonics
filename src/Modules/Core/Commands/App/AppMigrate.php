<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands\App;


use App\Modules\Core\Commands\Module\DatabaseMigrationAbstract;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO MIGRATE All Database in A SPECIFIC APP, RUN: php bin/console --app=TonicsCoupon --migrate
 *
 * Class ModuleMigrate
 * @package App\Commands\Module
 */
class AppMigrate extends DatabaseMigrationAbstract implements ConsoleCommand
{

    public function required(): array
    {
        return [
            "--app",
            "--migrate"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $s = DIRECTORY_SEPARATOR;
        $module = $commandOptions['--app'];
        $moduleDir = helper()->findAppDirectory($module) . "{$s}Database{$s}Migrations";
        if ($migrationFiles = helper()->findFilesWithExtension(['php'], $moduleDir)) {
            $migrationFiles = helper()->sortMigrationFiles($migrationFiles);
            foreach ($migrationFiles as $migrationFile) {
                $class = helper()->getFullClassName(file_get_contents($migrationFile));
                # This would reference the dbName in the migration table
                $dbMigrationName = strtolower(helper()->basePath($migrationFile));
                $this->handleMigrateUp($class, $dbMigrationName);
            }
        } else {
            $this->errorMessage("Nothing To Migrate in '{$commandOptions['--app']}' Directory");
        }
    }
}