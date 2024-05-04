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

class CreateRolePermissions_2023_05_07_220421 extends Migration {

    /**
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        $roleTable = Tables::getTable(Tables::ROLES);
        $permissionTable = Tables::getTable(Tables::PERMISSIONS);

        db(onGetDB: function (TonicsQuery $db) use ($permissionTable, $roleTable) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `fk_role_id` int(11) NOT NULL,
    `fk_permission_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (`fk_role_id`, `fk_permission_id`),
    CONSTRAINT `role_permissions_fk_role_id_foreign` FOREIGN KEY (`fk_role_id`) REFERENCES `$roleTable` (`role_id`)  ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `role_permissions_fk_permission_id_foreign` FOREIGN KEY (`fk_permission_id`) REFERENCES `$permissionTable` (`permission_id`)  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });

        Roles::UPDATE_DEFAULT_ROLES_PERMISSIONS();
    }


    /**
     * @throws \Exception
     */
    public function down(): void
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::ROLE_PERMISSIONS);
    }
}