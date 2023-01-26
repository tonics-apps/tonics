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

class CreatePurchaseTracksTable_2022_01_13_210710 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up(): void
    {
        $purchasesTable = Tables::getTable(Tables::PURCHASES);
        $tracksTable = Tables::getTable(Tables::TRACKS);

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `pt_id` int(10) unsigned NOT NULL AUTO_INCREMENT, -- pt_ meaning purchasetrack
  `fk_purchase_id` int(10) unsigned NOT NULL,
  `fk_track_id` int(10) unsigned NOT NULL,
  `price` decimal(6,2) NOT NULL, -- Store Total price, upto 999999.99
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
     * @throws \Exception
     */
    public function down(): void
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::PURCHASE_TRACKS);
    }
}