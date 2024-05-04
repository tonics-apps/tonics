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

namespace App\Modules\Core\Library;

use App\Modules\Core\Commands\Module\Traits\InitMigrationTable;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

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
     * @return TonicsQuery
     * @throws \Exception
     */
    public function getDB(): TonicsQuery
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
        $result = false;
        db(onGetDB: function (TonicsQuery $db) use ($tableName, &$result) {
            $result = $db->run("DROP TABLE IF EXISTS `$tableName`");
        });

        return $result;
    }
}