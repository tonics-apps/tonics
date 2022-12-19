<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
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
        $couponTableName = TonicsCouponActivator::couponTableName();
        $this->getDB()->run("
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
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable(TonicsCouponActivator::couponTableName());
    }
}