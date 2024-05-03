<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudContainerImages_2023_05_31_154705 extends Migration {

    public function up()
    {
        db(onGetDB: function (TonicsQuery $db)  {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `container_image_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `container_image_name` varchar(255) NOT NULL DEFAULT uuid(),
  `container_image_logo` varchar(255) DEFAULT NULL,
  `container_image_description` text DEFAULT NULL,
  `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
  `created_at` timestamp DEFAULT current_timestamp() ,
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  INDEX (`container_image_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });
    }

    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINER_IMAGES);
    }
}