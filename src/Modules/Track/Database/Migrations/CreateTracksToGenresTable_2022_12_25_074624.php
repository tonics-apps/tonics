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

namespace App\Modules\Track\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateTracksToGenresTable_2022_12_25_074624 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function ($db){
            $trackTable = Tables::getTable(Tables::TRACKS);
            $genreTable = Tables::getTable(Tables::GENRES);
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `fk_track_id` int(10) unsigned NOT NULL,
  `fk_genre_id` int(10) unsigned NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY `track_genre_fk_genre_id_foreign` (`fk_genre_id`),
  KEY `track_genre_fk_track_id_foreign` (`fk_track_id`),
  UNIQUE KEY unique_fk_track_id_fk_genre_id (fk_track_id, fk_genre_id),
  CONSTRAINT `track_genre_fk_genre_id_foreign` FOREIGN KEY (`fk_genre_id`) REFERENCES `$genreTable` (`genre_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `track_genre_fk_track_id_foreign` FOREIGN KEY (`fk_track_id`) REFERENCES `$trackTable` (`track_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
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
        return Tables::getTable(Tables::TRACK_GENRES);
    }
}