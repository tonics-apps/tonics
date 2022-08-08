<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateFieldTable_2022_01_04_141132 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        $this->getDB()->run("
        CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `field_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `field_name` varchar(255) NOT NULL,
  `field_slug` varchar(255) NOT NULL,
   `can_delete` tinyint(4) DEFAULT 1, -- 1 for deleteable and 0 for non-deleteable
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`field_id`),
  UNIQUE KEY `field_slug_unique` (`field_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        return $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::FIELD);
    }
}