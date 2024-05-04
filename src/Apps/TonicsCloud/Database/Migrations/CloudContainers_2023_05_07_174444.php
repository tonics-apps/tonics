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
        $serviceInstance = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
        db(onGetDB: function (TonicsQuery $db) use ($serviceInstance) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    `container_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `slug_id` UUID NOT NULL DEFAULT uuid(), -- thank goodness, we now have a UUID datatype in mariadb
    `container_name` varchar(255) NOT NULL DEFAULT uuid(),
    `container_description` text DEFAULT NULL,
    `container_status` varchar(30) DEFAULT 'Provisioning',
    `service_instance_id` int(10) unsigned NOT NULL,
    `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
    `created_at` timestamp DEFAULT current_timestamp() ,
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
     -- if not null, then the container has been deleted, we won't actually delete it, in case user wanna restore an instance from a backup or something similar 
    `end_time` DATETIME DEFAULT NULL,
    INDEX `slug_id_idx` (`slug_id`), -- it doesn't have to be unique, different customer can have the same slug_id
    INDEX `container_status_idx` (`container_status`),
    FULLTEXT KEY `container_name_fulltext_index` (`container_name`),
    CONSTRAINT `container_service_id_foreign` FOREIGN KEY (`service_instance_id`) REFERENCES `$serviceInstance` (`service_instance_id`) ON UPDATE CASCADE
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