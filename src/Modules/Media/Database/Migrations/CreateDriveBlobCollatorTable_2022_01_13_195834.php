<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Media\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateDriveBlobCollatorTable_2022_01_13_195834 extends Migration
{

    /**
     * @throws \JsonException
     */
    public function up()
    {
        $moreBlobInfo = json_encode([
            'corrupted' => false,
            'checksum' => null,
            'startSlice' => null,
            'endSlice' => null
        ], JSON_THROW_ON_ERROR);

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash_id` text DEFAULT NULL,
  `blob_name` mediumtext NOT NULL,
  `blob_chunk_part` int(10) unsigned,
  `blob_chunk_size` int(10) unsigned,
  `live_blob_chunk_size` int(10) signed NULL,
  `missing_blob_chunk_byte` int(10) GENERATED ALWAYS AS (cast(`blob_chunk_size` as signed) - cast(`live_blob_chunk_size` as signed)) STORED,
  `moreBlobInfo` longtext NOT NULL DEFAULT '$moreBlobInfo' CHECK (json_valid(`moreBlobInfo`)),
  PRIMARY KEY (id),
   UNIQUE KEY (`hash_id`) USING HASH
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::DRIVE_BLOB_COLLATOR);
    }
}