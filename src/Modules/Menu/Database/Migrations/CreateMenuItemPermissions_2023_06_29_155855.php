<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Menu\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreateMenuItemPermissions_2023_06_29_155855 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $menuItemsTable = Tables::getTable(Tables::MENU_ITEMS);
            $permissionTable = Tables::getTable(Tables::PERMISSIONS);
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `menu_item_permissions_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `fk_menu_item_slug_id` UUID NOT NULL,
  `fk_permission_id` INT unsigned NOT NULL,
  CONSTRAINT `menu_items_permission_foreign_to_menu_items` FOREIGN KEY (`fk_menu_item_slug_id`) REFERENCES `$menuItemsTable` (`slug_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `menu_items_permission_foreign_to_permissions` FOREIGN KEY (`fk_permission_id`) REFERENCES `$permissionTable` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });
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
        return Tables::getTable(Tables::MENU_ITEM_PERMISSION);
    }
}