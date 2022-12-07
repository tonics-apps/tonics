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

use App\Modules\Core\Commands\Module\Traits\InitMigrationTable;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO MIGRATE ALL THE DATABASE SCHEMA, RUN: php bin/console --migrate:all --fresh (drop all tables and re-migrate)
 *
 * Class MigrateAllFresh
 * @package App\Commands\Module
 */
class MigrateAllFresh extends DatabaseMigrationAbstract implements ConsoleCommand
{
    use InitMigrationTable;

    public function required(): array
    {
        return [
            "--migrate:all",
            "--fresh"
        ];
    }

    /**
     * @param array $commandOptions
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $this->forceDropTable();
        $this->initMigrationTable();
        $migrationFiles = helper()->getAllModuleMigrations();
        $appMigrationFiles = helper()->getAllModuleMigrations(helper()->getAllAppsDirectory());
        $migrationFiles = [...$migrationFiles, ...$appMigrationFiles];
        foreach ($migrationFiles as $migrationFile) {
            $class = helper()->getFullClassName(file_get_contents($migrationFile));
            # This would reference the dbName in the migration table
            $dbMigrationName = strtolower(helper()->basePath($migrationFile));
            $this->infoMessage("$dbMigrationName Migration Refreshing...");
            $this->handleMigrateUp($class, $dbMigrationName);
        }
    }
}