<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;

class CreateRolesTable_2020_01_00_001300 extends Migration
{

    /**
     * @throws Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db) {
            $db->run("
    CREATE TABLE IF NOT EXISTS `{$this->tableUser()}` (
        `id`  INT AUTO_INCREMENT PRIMARY KEY,
        `role_name` varchar(255) NOT NULL,
        `role_id` varchar(255) NOT NULL,
        `created_at` timestamp DEFAULT current_timestamp(),
        `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        UNIQUE KEY `role_name_unique` (`role_name`),
        UNIQUE KEY `role_id_unique` (`role_id`)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });
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
        return Tables::getTable(Tables::ROLES);
    }

}