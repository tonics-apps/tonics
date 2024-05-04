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