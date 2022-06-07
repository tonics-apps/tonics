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
        foreach ($migrationFiles as $migrationFile) {
            $class = $helper->getFullClassName(file_get_contents($migrationFile));
            # This would reference the dbName in the migration table
            $dbMigrationName = strtolower($helper->basePath($migrationFile));
            $this->handleMigrateUp($class, $dbMigrationName);
        }
    }
}