<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Menu\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AlterMenuTableAddCanEdit_2023_06_30_053743 extends Migration {

    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("ALTER TABLE `{$this->tableName()}` ADD COLUMN `menu_can_edit` BOOLEAN DEFAULT 1 AFTER `menu_slug`");
        });
    }

    public function down()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("ALTER TABLE `{$this->tableName()}` DROP COLUMN `menu_can_edit`;");
        });
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::MENUS);
    }
}