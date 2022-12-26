<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
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
        $trackTable = Tables::getTable(Tables::TRACKS);
        $genreTable = Tables::getTable(Tables::GENRES);
        
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fk_genre_id` int(10) unsigned NOT NULL,
  `fk_track_id` int(10) unsigned NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `track_genre_fk_genre_id_foreign` (`fk_genre_id`),
  KEY `track_genre_fk_track_id_foreign` (`fk_track_id`),
  CONSTRAINT `track_genre_fk_genre_id_foreign` FOREIGN KEY (`fk_genre_id`) REFERENCES `$genreTable` (`genre_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `track_genre_fk_track_id_foreign` FOREIGN KEY (`fk_track_id`) REFERENCES `$trackTable` (`track_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
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