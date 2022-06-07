<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreatePurchaseTracksTable_2022_01_13_210710 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $purchasesTable = Tables::getTable(Tables::PURCHASES);
        $tracksTable = Tables::getTable(Tables::TRACKS);

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `pt_id` int(10) unsigned NOT NULL AUTO_INCREMENT, -- pt_ meaning purchasetrack
  `fk_purchase_id` int(10) unsigned NOT NULL,
  `fk_track_id` int(10) unsigned NOT NULL,
  `price` decimal(6,2) NOT NULL, -- Store Total price, upto 9999.99
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`pt_id`),
  KEY `bt_purchase_tracks_fk_purchase_id_foreign` (`fk_purchase_id`),
  KEY `bt_purchase_tracks_fk_track_id_foreign` (`fk_track_id`),
  CONSTRAINT `bt_purchase_tracks_fk_purchase_id_foreign` FOREIGN KEY (`fk_purchase_id`) REFERENCES `$purchasesTable` (`purchase_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bt_purchase_tracks_fk_track_id_foreign` FOREIGN KEY (`fk_track_id`) REFERENCES `$tracksTable` (`track_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::PURCHASE_TRACKS);
    }
}