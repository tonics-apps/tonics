<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Menu\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateMenuLocationsTable_2022_01_13_201009 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        $menuTable = Tables::getTable(Tables::MENUS);

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `ml_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ml_name` varchar(255) NOT NULL,
  `ml_slug` varchar(255) NOT NULL,
  `fk_menu_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ml_id`),
  UNIQUE KEY `menu_locations_ml_slug_unique` (`ml_slug`),
  KEY `menu_locations_fk_menu_id_foreign` (`fk_menu_id`),
  CONSTRAINT `menu_locations_fk_menu_id_foreign` FOREIGN KEY (`fk_menu_id`) REFERENCES `$menuTable` (`menu_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Default Menu Populate
        $this->getDB()->run("
INSERT INTO {$this->tableName()}(ml_name, ml_slug, fk_menu_id)
VALUES 
    ('Header Section','header', 1),
    ('Footer Section','footer', 2);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down()
    {
        return $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::MENU_LOCATIONS);
    }
}