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
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use PDO;

abstract class DatabaseMigrationAbstract
{
    use ConsoleColor, InitMigrationTable;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->initMigrationTable();
    }

    /**
     * Handles the migration up command
     * @param string $class
     * @param string $migrationName
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function handleMigrateUp(string $class, string $migrationName): void
    {
        ## If migration doesn't already exist
        if (!$this->doesMigrationExist($migrationName) && is_subclass_of($class, Migration::class)) {
            # The container would resolve any dependency and kick the up function
            container()->get($class)->up();
            $this->insertMigrationRow($migrationName);
        }
    }

    /**
     * Handles the migration down command
     * @param $class
     * @param $migrationName
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function handleMigrateDown($class, $migrationName): void
    {
        if ($this->doesMigrationExist($migrationName) && is_subclass_of($class, Migration::class)) {
            container()->get($class)->down();
            db()->run("DELETE FROM {$this->migrationTableName()} WHERE migration = ?", $migrationName);
            $this->successMessage("$migrationName Migration Reversed");
        }
    }

    /**
     * @param $migrationName
     * @throws \Exception
     */
    public function insertMigrationRow($migrationName): void
    {
        db()->run("INSERT INTO {$this->migrationTableName()} (migration) VALUES(?)", $migrationName);
        # Migration message for the console
        $this->successMessage("$migrationName Migrated");
    }

    /**
     * @throws \Exception
     */
    public function forceDropTable(): void
    {
        db()->run("SET foreign_key_checks = 0");
        if ($tables = db()->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN)){
            foreach ($tables as $table){
                db()->query("DROP TABLE IF EXISTS `$table`");
            }
        }
        db()->query("SET foreign_key_checks = 1");
    }

    /**
     * @throws \Exception
     */
    public function doesMigrationExist($migrationName): bool
    {
        $result = db()
            ->run("SELECT EXISTS(SELECT * FROM {$this->migrationTableName()} WHERE migration = ?) AS result",
                $migrationName);

        if (is_array($result) && $result[0]->result === 0) {
            return false;
        } else {
            return true;
        }
    }

    private function migrationTableName(): string
    {
        return Tables::getTable(Tables::MIGRATIONS);
    }
}