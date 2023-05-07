<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

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
        $servicesTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
        $customerTable = Tables::getTable(Tables::CUSTOMERS);
        db(onGetDB: function (TonicsQuery $db) use ($customerTable, $servicesTable) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `service_instance_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `fk_service_id` int(10) unsigned NOT NULL,
  `fk_customer_id` BIGINT NOT NULL,
   start_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   end_time DATETIME,
  CONSTRAINT `service_provider_id_foreign` FOREIGN KEY (`fk_service_id`) REFERENCES `$servicesTable` (`service_id`) ON UPDATE CASCADE,
  CONSTRAINT `service_instances_fk_customer_id_foreign` FOREIGN KEY (`fk_customer_id`) REFERENCES `$customerTable` (`user_id`) ON UPDATE CASCADE
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