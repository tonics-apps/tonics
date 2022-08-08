<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Menu\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

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