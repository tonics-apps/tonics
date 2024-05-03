<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudContainerProfiles_2023_05_30_161939 extends Migration {

    /**
     * @return void
     * @throws \Exception
     */
    public function up(): void
    {
        db( onGetDB: function (TonicsQuery $db){
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `container_profile_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `container_profile_name` varchar(255) NOT NULL,
  `container_profile_description` text DEFAULT NULL,
  `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  UNIQUE KEY (`container_profile_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $db->Q()->insertOnDuplicate($this->tableName(), ContainerController::DEFAULT_PROFILES(), ['container_profile_description', 'others']);
        });
    }

    public function down()
    {
        $this->dropTable($this->tableName());
    }

    /**
     * @return string
     */
    private function tableName(): string
    {
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_PROFILES);
    }
}