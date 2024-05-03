<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\Schedules\CloudScheduleCheckCredits;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudServiceInstances_2023_05_07_174430 extends Migration {

    /**
     * @throws \Exception
     */
    public function up(): void
    {

        db(onGetDB: function (TonicsQuery $db){
            $servicesTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
            $customerTable = Tables::getTable(Tables::CUSTOMERS);
            $providerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);

            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    `service_instance_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `slug_id` UUID NOT NULL DEFAULT uuid(), -- thank goodness, we now have a UUID datatype in mariadb
    `provider_instance_id` varchar(255) DEFAULT NULL, -- id of the instance from the provider, e.g, id return by AWS or Linode, etc
    `service_instance_name` varchar(255) NOT NULL DEFAULT uuid(),
    `service_instance_status` varchar(30) DEFAULT 'Provisioning',
    `fk_provider_id` int(10) unsigned DEFAULT NULL,
    `fk_service_id` int(10) unsigned DEFAULT NULL,
    `fk_customer_id` BIGINT NOT NULL,
    `start_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `end_time` DATETIME DEFAULT NULL,
    `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `has_null_end_time` tinyint(1) AS (CASE WHEN `end_time` IS NULL THEN 1 ELSE NULL END) STORED,
    -- a generated column has_null_end_time is added, which stores a value of 1 if the end_time is NULL and NULL otherwise. 
    -- The UNIQUE constraint (`provider_instance_id`, `fk_provider_id`, `has_null_end_time`) ensures, 
    -- that the combination of provider_instance_id, fk_provider_id, and has_null_end_time is unique.
    -- this way, we can guarantee that only one instance of the instance is running from a provider
    INDEX `provider_instance_id_idx` (`provider_instance_id`),
    INDEX `service_instance_status_idx` (`service_instance_status`),
    UNIQUE `slug_id_idx` (`slug_id`), -- yh, the UUID, gotta be unique hia
    FULLTEXT KEY `service_instance_name_fulltext_index` (`service_instance_name`),
    CONSTRAINT `service_instances_fk_service_id_foreign` FOREIGN KEY (`fk_service_id`) REFERENCES `$servicesTable` (`service_id`) ON UPDATE CASCADE,
    CONSTRAINT `service_instances_fk_customer_id_foreign` FOREIGN KEY (`fk_customer_id`) REFERENCES `$customerTable` (`user_id`) ON UPDATE CASCADE,
    CONSTRAINT `services_instances_fk_provider_id_foreign` FOREIGN KEY (`fk_provider_id`) REFERENCES `$providerTable` (`provider_id`) ON UPDATE CASCADE,
    CONSTRAINT `uc_provider_instance_id_fk_provider_id` UNIQUE (`provider_instance_id`, `fk_provider_id`, `has_null_end_time`)
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
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
    }
}