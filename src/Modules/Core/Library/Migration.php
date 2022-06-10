<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Library;

use App\Modules\Core\Commands\Module\Traits\InitMigrationTable;

abstract class Migration
{
   use InitMigrationTable;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->initMigrationTable();
    }

    /**
     * @return MyPDO
     * @throws \Exception
     */
    public function getDB(): MyPDO
    {
        return db();
    }

    /**
     * @param $tableName
     * @return mixed
     * @throws \Exception
     */
    public function dropTable($tableName): mixed
    {
        return $this->getDB()->run("DROP TABLE IF EXISTS `$tableName`");
    }
}