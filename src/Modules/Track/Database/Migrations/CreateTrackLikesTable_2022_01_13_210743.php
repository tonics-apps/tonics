<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateTrackLikesTable_2022_01_13_210743 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $customersTable = Tables::getTable(Tables::CUSTOMERS);
        $tracksTable = Tables::getTable(Tables::TRACKS);

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fk_customer_id` BIGINT NOT NULL,
  `fk_track_id` int(10) unsigned NOT NULL,
  `is_like` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bt_track_likes_fk_track_id_unique` (`fk_track_id`),
  KEY `bt_track_likes_fk_customer_id_foreign` (`fk_customer_id`),
  CONSTRAINT `bt_track_likes_fk_customer_id_foreign` FOREIGN KEY (`fk_customer_id`) REFERENCES `$customersTable` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bt_track_likes_fk_track_id_foreign` FOREIGN KEY (`fk_track_id`) REFERENCES `$tracksTable` (`track_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
        return Tables::getTable(Tables::TRACK_LIKES);
    }
}