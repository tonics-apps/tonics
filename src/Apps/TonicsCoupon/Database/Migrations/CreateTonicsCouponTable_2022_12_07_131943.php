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

class CreateTonicsCouponTable_2022_12_07_131943 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function ($db){
            $couponTableName = TonicsCouponActivator::couponTableName();
            $db->run("
CREATE TABLE IF NOT EXISTS `{$couponTableName}` (
  `coupon_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug_id` char(16) DEFAULT NULL,
   `user_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `coupon_name` varchar(255) NOT NULL,
  `coupon_slug` varchar(255) NOT NULL,
  `coupon_status` tinyint(4) DEFAULT 0,
  `field_settings` longtext DEFAULT '{}' CHECK (json_valid(`field_settings`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `started_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY (`slug_id`),
  UNIQUE KEY `coupon_slug_unique` (`coupon_slug`),
  FULLTEXT KEY `coupon_fulltext_index` (`coupon_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        });
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable(TonicsCouponActivator::couponTableName());
    }
}