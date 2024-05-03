<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudServices_2023_05_07_174352 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {

        db(onGetDB: function (TonicsQuery $db) {
            $providerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `service_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `service_name` varchar(255) NOT NULL,
  `service_description` varchar(500) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT 'Server',
  `service_provider_id` int(10) unsigned NOT NULL,
  `monthly_rate` DECIMAL(10, 2) NOT NULL,
  `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  INDEX `service_type_idx` (`service_type`),
  UNIQUE KEY (`service_name`),
  CONSTRAINT `services_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `$providerTable` (`provider_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });

    }

    /**
     * @throws \Exception
     */
    public function down(): void
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
    }
}