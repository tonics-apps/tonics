<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Track\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateTracksCategories_2022_12_25_080501 extends Migration {

    public function up()
    {
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `track_cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug_id` char(16) DEFAULT NULL,
  `track_cat_parent_id` int(10) unsigned DEFAULT NULL,
  `track_cat_name` varchar(100) NOT NULL,
  `track_cat_slug` varchar(100) NOT NULL,
  `track_cat_status` tinyint(4) DEFAULT 1,
  `field_settings` longtext DEFAULT '{}' CHECK (json_valid(`field_settings`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`track_cat_id`),
  KEY `track_cat_parent_id_foreign` (`track_cat_parent_id`),
  CONSTRAINT `track_cat_parent_id_foreign` FOREIGN KEY (`track_cat_parent_id`) REFERENCES `{$this->tableName()}` (`track_cat_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down(): void
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::TRACK_CATEGORIES);
    }
}