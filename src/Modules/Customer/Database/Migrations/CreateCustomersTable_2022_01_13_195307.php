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

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;

class CreateCustomersTable_2022_01_13_195307 extends Migration
{

    /**
     * @throws Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db) {
            $settingsJSON = UserData::generateCustomerJSONSettings();
            $db->run("
    CREATE TABLE IF NOT EXISTS `{$this->tableCustomer()}` (
        `user_id`  BIGINT AUTO_INCREMENT PRIMARY KEY,
        `user_name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `email_verified_at` timestamp NULL DEFAULT NULL,
        `user_password` varchar(255) NOT NULL,
        `role` varchar(255) DEFAULT NULL,
        `is_guest` tinyint(1) NOT NULL DEFAULT 0,
        `settings` longtext NOT NULL DEFAULT '$settingsJSON' CHECK (json_valid(`settings`)),
        `created_at` timestamp DEFAULT current_timestamp(),
        `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        UNIQUE KEY `users_email_unique` (`email`)     
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });

    }

    /**
     * @throws Exception
     */
    public function down()
    {
        $this->dropTable($this->tableCustomer());
    }

    private function tableCustomer(): string
    {
        return Tables::getTable(Tables::CUSTOMERS);
    }

}