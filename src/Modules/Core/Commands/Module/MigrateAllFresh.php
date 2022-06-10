<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Commands\Module;

use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * TO MIGRATE ALL THE DATABASE SCHEMA, RUN: php bin/console --migrate:all --fresh (this run the down() method and the up() method)
 *
 * Class MigrateAllFresh
 * @package App\Commands\Module
 */
class MigrateAllFresh extends DatabaseMigrationAbstract implements ConsoleCommand
{

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
        $migrationFiles = helper()->getAllModuleMigrations();
        foreach ($migrationFiles as $migrationFile) {
            $class = helper()->getFullClassName(file_get_contents($migrationFile));
            # This would reference the dbName in the migration table
            $dbMigrationName = strtolower(helper()->basePath($migrationFile));
            $this->infoMessage("$dbMigrationName Migration Refreshing...");
            $this->handleMigrateUp($class, $dbMigrationName);
            $this->handleMigrateUp($class, $dbMigrationName);
        }
    }
}