<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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