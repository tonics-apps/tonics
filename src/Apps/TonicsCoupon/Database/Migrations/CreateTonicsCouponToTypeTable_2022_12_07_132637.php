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