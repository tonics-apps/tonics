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
use App\Modules\Track\Data\TrackData;
use Exception;

class CreateGenresTable_2022_01_13_210615 extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `genre_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `genre_name` varchar(250) NOT NULL,
  `genre_slug` varchar(255) NOT NULL,
  `genre_description` text DEFAULT NULL,
  `genre_status` tinyint(4) DEFAULT 1,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`genre_id`),
  UNIQUE KEY `bt_genres_genre_slug_unique` (`genre_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

       $genres = TrackData::defaultGenreData();
        ## loop over all the data -+ Inserting data to database using foreach loop might be slow depending on the number of rows u are inserting...
        ## What about storing the loop data into an array, and then dumping it once in d db, sound like a good isea.
        $GenresData = [];
        foreach ($genres as $genre) {
            $GenresData[] = [
                ## if an item in the data is "progressive-house", this turns it to "Progressive House"
                'genre_name' => ucwords(str_replace("-", " ", $genre)),
                ## slug would be progressive-house
                'genre_slug' => str_replace(" ", "-", $genre),
                'genre_description' => ucwords(str_replace("-", "", $genre))
            ];
        }
        ## Populate the genres tables With Default Genres Data
        $this->getDB()->insertOnDuplicate(table: $this->tableName(), data: $GenresData, update: ['genre_name', 'genre_description']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function down(): void
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::GENRES);
    }
}