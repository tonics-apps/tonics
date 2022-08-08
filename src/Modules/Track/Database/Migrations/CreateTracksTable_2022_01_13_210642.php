<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateTracksTable_2022_01_13_210642 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        $artistTable = Tables::getTable(Tables::ARTISTS);
        $genreTable = Tables::getTable(Tables::GENRES);
        $licenseTable = Tables::getTable(Tables::LICENSES);

        // unique_id should be one of licenseAttr License
        $licenseAttrJSON = json_encode([
            'unique_id' => 'url_download',
            'unique_id_2' => 'url_download_2',
        ], JSON_THROW_ON_ERROR);

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS {$this->tableName()} (
  `track_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug_id` char(16) DEFAULT NULL,
  `track_slug` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `audio_url` varchar(255) DEFAULT NULL,
  `track_title` varchar(200) NOT NULL,
  `track_plays` int(11) DEFAULT 0,
  `track_bpm` int(11) DEFAULT NULL,
  `track_status` tinyint(4) NOT NULL DEFAULT 0,
  `license_attr_id_link` longtext NOT NULL DEFAULT '$licenseAttrJSON' CHECK (json_valid(`license_attr_id_link`)),
  `field_settings` longtext DEFAULT '{}' CHECK (json_valid(`field_settings`)),
  `fk_genre_id` int(10) unsigned NOT NULL,
  `fk_artist_id` int(10) unsigned NOT NULL,
  `fk_license_id` int(10) unsigned NOT NULL,
  `field_ids` longtext DEFAULT '{}' CHECK (json_valid(`field_ids`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`track_id`),
  UNIQUE KEY (`slug_id`),
  UNIQUE KEY `bt_tracks_track_slug_unique` (`track_slug`),
  KEY `bt_tracks_fk_genre_id_foreign` (`fk_genre_id`),
  KEY `bt_tracks_fk_artist_id_foreign` (`fk_artist_id`),
  KEY `bt_tracks_fk_license_id_foreign` (`fk_license_id`),
  CONSTRAINT `bt_tracks_fk_artist_id_foreign` FOREIGN KEY (`fk_artist_id`) REFERENCES `$artistTable` (`artist_id`) ON UPDATE CASCADE,
  CONSTRAINT `bt_tracks_fk_genre_id_foreign` FOREIGN KEY (`fk_genre_id`) REFERENCES `$genreTable` (`genre_id`) ON UPDATE CASCADE,
  CONSTRAINT `bt_tracks_fk_license_id_foreign` FOREIGN KEY (`fk_license_id`) REFERENCES `$licenseTable` (`license_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::TRACKS);
    }
}