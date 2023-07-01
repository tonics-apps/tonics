<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Menu\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AlterMenuItemsTableAddSlugID_2023_06_30_053220 extends Migration {

    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("ALTER TABLE `{$this->tableName()}` ADD COLUMN `slug_id` UUID NOT NULL DEFAULT uuid() AFTER `mt_parent_id`");
            $db->run("ALTER TABLE `{$this->tableName()}` ADD CONSTRAINT slug_id_idx UNIQUE(slug_id);");
        });
    }

    public function down()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("ALTER TABLE `{$this->tableName()}` DROP COLUMN `slug_id`;");
            $db->run("ALTER TABLE `{$this->tableName()}` DROP CONSTRAINT `slug_id_idx`;");
        });
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::MENU_ITEMS);
    }
}