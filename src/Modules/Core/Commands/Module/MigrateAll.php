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

use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO MIGRATE ALL THE DATABASE SCHEMA, RUN: php bin/console --migrate:all
 *
 * Class MigrateAll
 * @package App\Commands\Module
 */
class MigrateAll extends DatabaseMigrationAbstract implements ConsoleCommand
{

    public function required(): array
    {
       return [
           "--migrate:all"
       ];
    }

    /**
     * @param array $commandOptions
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $helper = helper();
        $migrationFiles = $helper->getAllModuleMigrations();
        $appMigrationFiles = $helper->getAllModuleMigrations($helper->getAllAppsDirectory());
        $migrationFiles = [...$migrationFiles, ...$appMigrationFiles];
        foreach ($migrationFiles as $migrationFile) {
            $class = $helper->getFullClassName(file_get_contents($migrationFile));
            # This would reference the dbName in the migration table
            $dbMigrationName = strtolower($helper->basePath($migrationFile));
            $this->handleMigrateUp($class, $dbMigrationName);
        }
    }
}