<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Field\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateFieldTable_2022_01_04_141132 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        $this->getDB()->run("
        CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `field_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `field_name` varchar(255) NOT NULL,
  `field_slug` varchar(255) NOT NULL,
   `can_delete` tinyint(4) DEFAULT 1, -- 1 for deleteable and 0 for non-deleteable
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        return $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::FIELD);
    }
}