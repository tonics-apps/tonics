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

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreatePermissions_2023_05_07_220417 extends Migration
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function up ()
    {
        db(onGetDB: function (TonicsQuery $db) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    permission_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_display_name VARCHAR(255) DEFAULT NULL,
    permission_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `role_permission_name_unique` (`permission_name`),
    FULLTEXT KEY `post_fulltext_index` (`permission_display_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });

        Roles::UPDATE_DEFAULT_PERMISSIONS();
    }


    /**
     * @throws \Exception
     */
    public function down ()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName (): string
    {
        return Tables::getTable(Tables::PERMISSIONS);
    }
}