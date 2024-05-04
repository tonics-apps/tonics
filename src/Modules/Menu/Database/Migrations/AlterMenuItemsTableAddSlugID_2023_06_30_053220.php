<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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