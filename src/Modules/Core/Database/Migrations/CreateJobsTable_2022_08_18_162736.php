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

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreateJobsTable_2022_08_18_162736 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {

        db(onGetDB: function (TonicsQuery $db){
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
     `job_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      job_parent_id bigint(20) unsigned DEFAULT NULL,
     `job_name` varchar(255) DEFAULT NULL,
     `job_status` enum('queued', 'in_progress', 'processed','failed') NOT NULL DEFAULT 'queued',
     `job_priority` tinyint(2) NOT NULL DEFAULT 5, -- 10 is the highest priority
     -- `job_priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
     `job_data` JSON DEFAULT NULL,
     `created_at` timestamp DEFAULT current_timestamp(),
     `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
     `time_completed` timestamp NULL DEFAULT NULL,
     -- KEY AND INDEX ARE THE SAME THING IN MARIADB
     KEY `job_group_name_idx` (`job_name`),
     INDEX `job_status_idx` (`job_status`),
     INDEX jobs_parent_id_status_idx (job_parent_id, job_status),
     CONSTRAINT `job_parent_id_foreign` FOREIGN KEY (`job_parent_id`) REFERENCES `{$this->tableName()}` (`job_id`) ON UPDATE CASCADE ON DELETE CASCADE,
     PRIMARY KEY (`job_id`)
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
        return Tables::getTable(Tables::JOBS);
    }
}