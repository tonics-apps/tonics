<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateGlobalTable_2022_06_08_214142 extends Migration
{

    /**
     * @throws \Exception
     */
    public function up()
    {
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,     
    `key` varchar(500) NOT NULL,
    `value` JSON DEFAULT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::GLOBAL);
    }
}