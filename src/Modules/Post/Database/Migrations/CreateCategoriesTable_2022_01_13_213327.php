<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Post\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateCategoriesTable_2022_01_13_213327 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        ## This would be the category for the Post table. it is not related to track table

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug_id` char(16) DEFAULT NULL,
  `cat_parent_id` int(10) unsigned DEFAULT NULL,
  `cat_name` varchar(100) NOT NULL,
  `cat_slug` varchar(100) NOT NULL,
  `cat_url_slug` varchar(255) DEFAULT NULL,
  `cat_content` text DEFAULT NULL,
  `cat_status` tinyint(4) DEFAULT 1,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`cat_id`),
  KEY `cat_parent_id_foreign` (`cat_parent_id`),
  CONSTRAINT `cat_parent_id_foreign` FOREIGN KEY (`cat_parent_id`) REFERENCES `{$this->tableName()}` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

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
        return Tables::getTable(Tables::CATEGORIES);
    }
}