<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Track\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateTrackWishListsTable_2022_12_25_091628 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        $customersTable = Tables::getTable(Tables::CUSTOMERS);
        
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `track_wl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_customer_id` BIGINT NOT NULL,
  `track_id` int(11) NOT NULL, -- the track id of the wishlist for relationship stuffy.
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`track_wl_id`),
  KEY `bt_wish_lists_fk_customer_id_foreign` (`fk_customer_id`),
  CONSTRAINT `bt_wish_lists_fk_customer_id_foreign` FOREIGN KEY (`fk_customer_id`) REFERENCES `$customersTable` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
        return Tables::getTable(Tables::TRACK_WISH_LIST);
    }
}