<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueue_2023_06_03_230154 extends Migration {

    public function up()
    {
        db( onGetDB: function (TonicsQuery $db){
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    `job_queue_id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_queue_parent_job_id` INT,
    `job_queue_name` VARCHAR(255) NOT NULL,
    `job_queue_status` ENUM('queued', 'in_progress', 'failed', 'processed') DEFAULT 'queued',
    -- The higher the value, the higher the priority, 10 has an higher prioty that say 2, 5 has higher than 4, etc
    -- When they have the samee priority, we use fifo (first come first serve)
    -- The lowest priority is 0, if we ever get to a level, where multiple jobs have reached their lowest priority which is 0,
    -- we stop decrementing, and use FIFO (first in first out)(first come first serve)
    `job_queue_priority` TINYINT UNSIGNED NOT NULL DEFAULT 100,
    `job_queue_data` JSON DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    `job_retry_after` timestamp NULL DEFAULT NULL,
    `job_attempts` tinyint(3) unsigned NOT NULL DEFAULT 30,
    KEY `job_queue_status_idx` (`job_queue_status`),
    KEY `job_queue_parent_job_id_idx` (`job_queue_parent_job_id`),
    KEY idx_priority_id_covering (job_queue_priority DESC, job_queue_id ASC),
    KEY (`job_queue_name`),
    CONSTRAINT `job_queue_child_to_parent_foreign` FOREIGN KEY (`job_queue_parent_job_id`) REFERENCES `{$this->tableName()}` (`job_queue_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        });
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    /**
     * @return string
     */
    private function tableName(): string
    {
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_JOBS_QUEUE);
    }
}