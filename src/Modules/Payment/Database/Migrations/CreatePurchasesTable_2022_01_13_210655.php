<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
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
  `total_price` decimal(15, 2) DEFAULT NULL,
  `payment_status` enum('pending','processing','completed','decline') NOT NULL DEFAULT 'pending',
  `others` longtext NOT NULL DEFAULT '$othersJSON' CHECK (json_valid(`others`)),
  `invoice_id` VARCHAR(255) GENERATED ALWAYS AS (JSON_EXTRACT(others, '$.invoice_id')) STORED,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY (`slug_id`),
  PRIMARY KEY (`purchase_id`),
  INDEX invoice_id_index (`invoice_id`),
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