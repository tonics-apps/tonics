<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Commands\Module;

use App\Modules\Core\Commands\Module\Traits\InitMigrationTable;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

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
            container()->get($class)->down(); $tbl = $this->migrationTableName();
            db(onGetDB: function (TonicsQuery $db) use ($tbl, $migrationName, &$result) {
                $db->FastDelete($tbl, db()->WhereIn(table()->getColumn($tbl, 'migration'), $migrationName));
                $this->successMessage("$migrationName Migration Reversed");
            });
        }
    }

    /**
     * @param $migrationName
     * @throws \Exception
     */
    public function insertMigrationRow($migrationName): void
    {
        db(onGetDB: function (TonicsQuery $db) use ($migrationName, &$result) {
            $db->Insert($this->migrationTableName(), ['migration' => $migrationName]);
            # Migration message for the console
            $this->successMessage("$migrationName Migrated");
        });
    }

    /**
     * @throws \Exception
     */
    public function forceDropTable(): void
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->query("SET foreign_key_checks = 0");

            db(onGetDB: function ($db) {
                $stm = $db->getPdo()->prepare("SHOW TABLES");
                $stm->execute();
                if ($tables = $stm->fetchAll(\PDO::FETCH_COLUMN, 0)){
                    foreach ($tables as $table){
                        $db->query("DROP TABLE IF EXISTS `$table`");
                    }
                }
            });

            $db->query("SET foreign_key_checks = 1");
        });
    }

    /**
     * @throws \Exception
     */
    public function doesMigrationExist($migrationName): bool
    {
        $result = null;
        db(onGetDB: function (TonicsQuery $db) use ($migrationName, &$result) {
            $result = $db
                ->run("SELECT EXISTS(SELECT * FROM {$this->migrationTableName()} WHERE migration = ?) AS result",
                    $migrationName);
        });

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