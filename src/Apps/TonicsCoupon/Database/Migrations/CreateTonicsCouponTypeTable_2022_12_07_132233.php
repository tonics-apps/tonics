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

class CreateTonicsCouponTypeTable_2022_12_07_132233 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function ($db){
            ## This would be the category for the Post table. it is not related to track table
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `coupon_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug_id` char(16) DEFAULT NULL,
  `coupon_type_parent_id` int(10) unsigned DEFAULT NULL,
  `coupon_type_name` varchar(100) NOT NULL,
  `coupon_type_slug` varchar(100) NOT NULL,
  `coupon_type_status` tinyint(4) DEFAULT 1,
  `field_settings` longtext DEFAULT '{}' CHECK (json_valid(`field_settings`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`coupon_type_id`),
  KEY `coupon_type_parent_id_foreign` (`coupon_type_parent_id`),
  CONSTRAINT `coupon_type_parent_id_foreign` FOREIGN KEY (`coupon_type_parent_id`) REFERENCES `{$this->tableName()}` (`coupon_type_id`) ON UPDATE CASCADE
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
        return TonicsCouponActivator::couponTypeTableName();
    }
}