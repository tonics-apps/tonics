<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCoupon\Database\Migrations;

use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateTonicsCouponToTypeTable_2022_12_07_132637 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function ($db){
            ## Many To Many Rel --- A Coupon May Have Many CouponTypes and Likewise,
            ##  A CouponTypes Can Belong To Many Coupons
            $couponTable = TonicsCouponActivator::couponTableName();
            $couponTypeTableName = TonicsCouponActivator::couponTypeTableName();

            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fk_coupon_type_id` int(10) unsigned NOT NULL,
  `fk_coupon_id` int(10) unsigned NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `bt_coupon_categories_fk_coupon_type_id_foreign` (`fk_coupon_type_id`),
  KEY `bt_coupon_categories_fk_coupon_id_foreign` (`fk_coupon_id`),
  CONSTRAINT `bt_coupon_categories_fk_coupon_type_id_foreign` FOREIGN KEY (`fk_coupon_type_id`) REFERENCES `$couponTypeTableName` (`coupon_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bt_coupon_categories_fk_coupon_id_foreign` FOREIGN KEY (`fk_coupon_id`) REFERENCES `$couponTable` (`coupon_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });
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
        return TonicsCouponActivator::couponToTypeTableName();
    }
}