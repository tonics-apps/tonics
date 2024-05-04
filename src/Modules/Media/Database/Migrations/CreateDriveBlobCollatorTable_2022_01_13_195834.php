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