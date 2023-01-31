<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateSchedulerTable_2022_08_19_014455 extends Migration
{

    /**
     * @throws \Exception
     */
    public function up()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
     `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `schedule_name` varchar(255) NOT NULL,
     `schedule_parent_name` varchar(255) DEFAULT NULL,
     `schedule_priority` tinyint unsigned NOT NULL DEFAULT 5, -- max of tinyint (255) is the highest priority
     `schedule_data` JSON DEFAULT NULL,
     `schedule_ticks` int(10) unsigned DEFAULT 1, -- no of time job has been scheduled or ticked
     -- no of parallelization, 1 should be enough most of the time, go above if your code is built with parallization in mind
     `schedule_parallel` tinyint unsigned NOT NULL DEFAULT 1,
     `schedule_every` int(10) signed DEFAULT 120, -- default is every 2 minute, note that job_schedule_every is in second
     -- Whenever we have changes in any column, the next_run gets computed, this would usually be when we increment the tick
      `schedule_next_run` timestamp GENERATED ALWAYS AS (DATE_ADD(updated_at, INTERVAL schedule_every SECOND)) VIRTUAL, 
      `created_at` timestamp DEFAULT current_timestamp(),
       `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
     PRIMARY KEY (`schedule_id`),
     UNIQUE KEY `schedule_name_index` (`schedule_name`),
     KEY `schedule_parent_name_index` (`schedule_parent_name`),
      CONSTRAINT `schedule_child_to_parent_foreign` FOREIGN KEY (`schedule_parent_name`) REFERENCES `{$this->tableName()}` (`schedule_name`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $this->getDB()->run($sql);
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
        return Tables::getTable(Tables::SCHEDULER);
    }
}