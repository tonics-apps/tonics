<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Exception;

class CreateUsersTable_2020_01_00_001340 extends Migration
{

    /**
     * @throws Exception
     */
    public function up()
    {
        $settingsJSON = UserData::generateAdminJSONSettings();
    $this->getDB()->run("
    CREATE TABLE IF NOT EXISTS `{$this->tableUser()}` (
        `user_id`  BIGINT AUTO_INCREMENT PRIMARY KEY,
        `user_name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `email_verified_at` timestamp NULL DEFAULT NULL,
        `user_password` varchar(255) NOT NULL,
        `role` varchar(255) NOT NULL,
        `settings` longtext NOT NULL DEFAULT '$settingsJSON' CHECK (json_valid(`settings`)),
        `created_at` timestamp DEFAULT current_timestamp(),
        `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        UNIQUE KEY `users_email_unique` (`email`)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * @throws Exception
     */
    public function down()
    {
        $this->dropTable($this->tableUser());
    }

    private function tableUser(): string
    {
        return Tables::getTable(Tables::USERS);
    }

}