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

namespace App\Modules\Field\Database\Migrations;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreateFieldItemsTable_2022_01_05_161811 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {

        db(onGetDB: function (TonicsQuery $db){
            $fieldTable = Tables::getTable(Tables::FIELD);
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fk_field_id` int(10) unsigned NOT NULL,
  `field_id` bigint(20) unsigned NOT NULL,
  `field_parent_id` bigint(20) unsigned DEFAULT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_options` longtext NOT NULL CHECK (json_valid(`field_options`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `bt_field_items_fk_field_id_foreign` (`fk_field_id`),
  CONSTRAINT `bt_field_items_fk_field_id_foreign` FOREIGN KEY (`fk_field_id`) REFERENCES `$fieldTable` (`field_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`field_options` is null or json_valid(`field_options`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });

        (new FieldData())->importFieldItems(FieldConfig::DefaultFieldItems());
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        return $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::FIELD_ITEMS);
    }
}