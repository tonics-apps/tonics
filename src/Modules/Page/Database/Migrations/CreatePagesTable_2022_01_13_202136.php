<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Page\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreatePagesTable_2022_01_13_202136 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up(){

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `field_ids` longtext DEFAULT '{}' CHECK (json_valid(`field_ids`)),
  `page_title` varchar(255) NOT NULL,
  `page_slug` varchar(255) NOT NULL,
  `page_status` tinyint(4) NOT NULL DEFAULT 1,  
  `field_settings` longtext DEFAULT '{}' CHECK (json_valid(`field_settings`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `pages_page_slug_unique` (`page_slug`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::PAGES);
    }
}