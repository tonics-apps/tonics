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

namespace App\Modules\Customer\Database\Migrations;

use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AlterCustomersTableAddRoleForeignKeyConstraint_2023_01_19_099554 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("ALTER TABLE `{$this->tableCustomer()}` DROP COLUMN `role`;");
            $db->run("ALTER TABLE `{$this->tableCustomer()}` ADD COLUMN `role` INT AFTER `user_password`");
            $db->run("ALTER TABLE `{$this->tableCustomer()}` ADD FOREIGN KEY (`role`) REFERENCES `{$this->tableRole()}`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT;");
        });
    }

    public function tableCustomer(): string
    {
       return Tables::getTable(Tables::CUSTOMERS);
    }

    public function tableRole(): string
    {
        return Tables::getTable(Tables::ROLES);
    }

    public function down()
    {
        $this->getDB()->run("");
    }
}