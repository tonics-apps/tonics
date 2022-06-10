<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Field\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateFieldItemsTable_2022_01_05_161811 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        $fieldTable = Tables::getTable(Tables::FIELD);
        $this->getDB()->run("
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
    }

    public function down()
    {
        return $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::FIELD_ITEMS);
    }
}