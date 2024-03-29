<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Media\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreateDriveBlobCollatorTable_2022_01_13_195834 extends Migration
{

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public function up()
    {

        db(onGetDB: function (TonicsQuery $db){
            $moreBlobInfo = json_encode([
                'corrupted' => false,
                'checksum' => null,
                'startSlice' => null,
                'endSlice' => null
            ], JSON_THROW_ON_ERROR);

            $db->run("
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
        });
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::DRIVE_BLOB_COLLATOR);
    }
}