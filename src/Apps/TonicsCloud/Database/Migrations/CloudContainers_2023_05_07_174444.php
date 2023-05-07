<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudContainers_2023_05_07_174444 extends Migration {
    /**
     * @throws \Exception
     */
    public function up()
    {
        $serviceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);
        db(onGetDB: function (TonicsQuery $db) use ($serviceTable) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `container_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `container_name` int(10) unsigned NOT NULL,
  `container_description` text DEFAULT NULL,
  `container_service_id` int(10) unsigned NOT NULL,
  `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
  `created_at` timestamp DEFAULT current_timestamp() ,
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`container_id`),
  CONSTRAINT `container_service_id_foreign` FOREIGN KEY (`container_service_id`) REFERENCES `$serviceTable` (`service_id`) ON UPDATE CASCADE
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
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
    }
}