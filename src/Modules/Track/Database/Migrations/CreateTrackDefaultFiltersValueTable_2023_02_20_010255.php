<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Track\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use App\Modules\Track\Data\TrackData;

class CreateTrackDefaultFiltersValueTable_2023_02_20_010255 extends Migration
{

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function ($db){
            $trackDefaultFiltersValueTable = Tables::getTable(Tables::TRACK_DEFAULT_FILTERS);
            $db->run("
        CREATE TABLE IF NOT EXISTS $trackDefaultFiltersValueTable (
            tdf_id int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tdf_name VARCHAR(255),
            tdf_type ENUM('bpm', 'mood', 'instrument', 'key', 'genre', 'artist', 'samplePackType', 'acapellaGender', 'acapellaVocalStyle', 'acapellaEmotion', 'acapellaScale', 'acapellaEffects'),
            INDEX idx_tdvf_name (tdf_name),
            UNIQUE KEY unique_tdf_type_name (tdf_type, tdf_name)
    );
");
        });

        self::insertDefaultFiltersValueData();
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable(Tables::getTable(Tables::TRACK_DEFAULT_FILTERS));
    }

    /**
     * @throws \Exception
     */
    public static function insertDefaultFiltersValueData()
    {
        db(onGetDB: function ($db){
            $trackDefaultFiltersValueTable = Tables::getTable(Tables::TRACK_DEFAULT_FILTERS);
            ## Populate the track_default_filters tables With Default Genres Data
            $db->insertOnDuplicate($trackDefaultFiltersValueTable, TrackData::defaultFilterData(), update: ['tdf_name']);
        });
    }
}