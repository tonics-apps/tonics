<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Menu\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateMenuItemsTable_2022_01_13_200954 extends Migration {

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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_menu_id` int(10) unsigned NOT NULL,
  `mt_id` int(10) unsigned NOT NULL,
  `mt_parent_id` int(10) unsigned DEFAULT NULL,
  `mt_name` varchar(500) NOT NULL,
  `mt_icon` varchar(500) DEFAULT NULL,
  `mt_classes` varchar(500) DEFAULT NULL,
  `mt_target` varchar(10) DEFAULT NULL,
  `mt_url_slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `menu_items_fk_menu_id_foreign` (`fk_menu_id`),
  CONSTRAINT `menu_items_fk_menu_id_foreign` FOREIGN KEY (`fk_menu_id`) REFERENCES `$menuTable` (`menu_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
        return $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::MENU_ITEMS);
    }
}