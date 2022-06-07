<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Widget\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateWidgetLocationsTable_2022_01_13_213000 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $widgetTable = Tables::getTable(Tables::WIDGETS);
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `wl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wl_name` varchar(255) NOT NULL,
  `wl_slug` varchar(255) NOT NULL,
  `fk_widget_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`wl_id`),
  UNIQUE KEY `bt_widget_locations_wl_slug_unique` (`wl_slug`),
  KEY `bt_widget_locations_fk_widget_id_foreign` (`fk_widget_id`),
  CONSTRAINT `bt_widget_locations_fk_widget_id_foreign` FOREIGN KEY (`fk_widget_id`) REFERENCES `$widgetTable` (`widget_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->getDB()->run("
INSERT INTO {$this->tableName()}(wl_name, wl_slug, fk_widget_id)
VALUES 
    ('Sidebar Section','sidebar-section', 1);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::WIDGET_LOCATIONS);
    }
}