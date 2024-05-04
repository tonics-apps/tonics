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

class CreateTrackLikesTable_2022_01_13_210743 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function ($db){
            $customersTable = Tables::getTable(Tables::CUSTOMERS);
            $tracksTable = Tables::getTable(Tables::TRACKS);
            $db->run("
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

        });
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
        return Tables::getTable(Tables::TRACK_LIKES);
    }
}