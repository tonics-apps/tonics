<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudServiceInstanceUsageLog_2023_05_18_172733 extends Migration {

    public function up()
    {
        $serviceInstance = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
        db(onGetDB: function (TonicsQuery $db) use ($serviceInstance) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `service_instance_id` int(10) unsigned NOT NULL,
  `log_description` varchar(255) NOT NULL,
  `usage_data` longtext DEFAULT '{}' CHECK (json_valid(`usage_data`)),
  `created_at` timestamp DEFAULT current_timestamp() ,
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  CONSTRAINT `services_instance_log_service_instance_id_foreign` FOREIGN KEY (`service_instance_id`) REFERENCES `$serviceInstance` (`service_instance_id`) ON UPDATE CASCADE
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


    /**
     * @return string
     */
    private function tableName(): string
    {
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG);
    }
}