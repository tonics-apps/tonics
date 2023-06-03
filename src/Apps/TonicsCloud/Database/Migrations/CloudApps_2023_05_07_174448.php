<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudApps_2023_05_07_174448 extends Migration {

    /**
     * @return void
     * @throws \Exception
     */
    public function up(): void
    {
        db(onGetDB: function (TonicsQuery $db)  {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `app_name` varchar(255) NOT NULL DEFAULT uuid(),
  `app_description` text DEFAULT NULL,
  `app_version` VARCHAR(255) NOT NULL,
  `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
  `created_at` timestamp DEFAULT current_timestamp() ,
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   INDEX (`app_name`)
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
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS);
    }
}