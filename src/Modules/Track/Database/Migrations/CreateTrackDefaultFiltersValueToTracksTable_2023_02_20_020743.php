<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Track\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateTrackDefaultFiltersValueToTracksTable_2023_02_20_020743 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function ($db){
            $trackDefaultFiltersValueTable = Tables::getTable(Tables::TRACK_DEFAULT_FILTERS);
            $trackDefaultFiltersToTracksTable = Tables::getTable(Tables::TRACK_DEFAULT_FILTERS_TRACKS);
            $tracksTable = Tables::getTable(Tables::TRACKS);

            $db->run("
    CREATE TABLE IF NOT EXISTS $trackDefaultFiltersToTracksTable (
        `id` bigint(20)  unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `fk_track_id` int(10) unsigned NOT NULL,
        `fk_tdf_id` int(10) unsigned NOT NULL,
         KEY `tdft_fk_track_id_foreign` (`fk_track_id`),
         KEY `tdft_fk_tdf_id_foreign` (`fk_tdf_id`),
        UNIQUE KEY unique_fk_track_id_fk_tdf_id (fk_track_id, fk_tdf_id),
        CONSTRAINT `tdf_fk_track_id_foreign` FOREIGN KEY (`fk_track_id`) REFERENCES `$tracksTable` (`track_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `tdf_fk_tdf_id_foreign` FOREIGN KEY (`fk_tdf_id`) REFERENCES `$trackDefaultFiltersValueTable` (`tdf_id`) ON DELETE CASCADE ON UPDATE CASCADE
    );
");
        });
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable(Tables::getTable(Tables::TRACK_DEFAULT_FILTERS_TRACKS));
    }
}