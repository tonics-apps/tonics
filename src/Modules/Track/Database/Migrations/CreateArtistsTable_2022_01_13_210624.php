<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateArtistsTable_2022_01_13_210624 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `artist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artist_name` varchar(255) NOT NULL,
  `artist_slug` varchar(255) NOT NULL,
  `artist_bio` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`artist_id`),
  UNIQUE KEY `bt_artists_artist_slug_unique` (`artist_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $defaultArtist[] = [
            'artist_name' => 'Unknown',
            'artist_slug' => 'unknown',
            'artist_bio' => 'I am unknown',
        ];
        $this->getDB()->insertOnDuplicate(table: $this->tableName(), data: $defaultArtist, update: ['artist_name', 'artist_bio']);

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
        return Tables::getTable(Tables::ARTISTS);
    }
}