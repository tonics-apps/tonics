<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;
use JsonException;

class CreatePurchasesTable_2022_01_13_210655 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws JsonException
     */
    public function up()
    {
        $othersJSON = json_encode([
            'itemIds' => null, // would be used to confirm the item the user is actually buying
            'invoice_id' => null,
            'tx_ref' => null, // this is for flutterwave
            'order_id' => null, // this is for PayPal
            'payment_method' => null, // i.e PayPal, FlutterWave
        ], JSON_THROW_ON_ERROR);

        $customerTable = Tables::getTable(Tables::CUSTOMERS);

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `purchase_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug_id` char(64) DEFAULT NULL,
  `fk_customer_id` BIGINT NOT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('pending','processing','completed','decline') NOT NULL DEFAULT 'pending',
  `others` longtext NOT NULL DEFAULT '$othersJSON' CHECK (json_valid(`others`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY (`slug_id`),
  PRIMARY KEY (`purchase_id`),
  KEY `purchases_fk_customer_id_foreign` (`fk_customer_id`),
  CONSTRAINT `purchases_fk_customer_id_foreign` FOREIGN KEY (`fk_customer_id`) REFERENCES `$customerTable` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
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
        return Tables::getTable(Tables::PURCHASES);
    }
}