<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreateSessionTable_2022_01_13_124434 extends Migration
{

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
     `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
     -- `active` tinyint(2) NOT NULL DEFAULT 1,
  `session_id` varchar(255) NOT NULL,
  `session_data` JSON DEFAULT NULL,
  -- By default the session would expire in an hour
  `updated_at` timestamp NOT NULL DEFAULT (current_timestamp() + INTERVAL 12 HOUR),
   PRIMARY KEY (`id`),
   UNIQUE KEY `primaryid_sessionid_unique` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::SESSIONS);
    }
}