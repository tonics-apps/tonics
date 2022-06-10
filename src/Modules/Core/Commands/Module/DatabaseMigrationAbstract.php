<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
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
    public function handleMigrateUp(string $class, string $migrationName)
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
    public function handleMigrateDown($class, $migrationName)
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
    public function insertMigrationRow($migrationName)
    {
        db()->run("INSERT INTO {$this->migrationTableName()} (migration) VALUES(?)", $migrationName);
        # Migration message for the console
        $this->successMessage("$migrationName Migrated");
    }

    /**
     * @throws \Exception
     */
    public function forceDropTable()
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