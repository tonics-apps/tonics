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
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudAppsContainers_2023_05_07_174501 extends Migration {


    /**
     * @throws \Exception
     */
    public function up(): void
    {
        $tonicsCloudContainer = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
        $tonicsCloudApps = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS);
        db(onGetDB: function (TonicsQuery $db) use ($tonicsCloudApps, $tonicsCloudContainer) {

            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `app_status_msg` varchar(255) DEFAULT 'Okay',
    `app_status` varchar(30) DEFAULT 'Running',
    `fk_container_id` int(10) unsigned NOT NULL,
    `fk_app_id` int(10) unsigned NOT NULL,
    `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    INDEX `app_status_idx` (`app_status`),
    UNIQUE `cloud_apps_container_unique` (`fk_app_id`, `fk_container_id`), -- you can't have multiple apps of the same version in a container, however, you can different version if there is one
    KEY `cloud_apps_containers_fk_container_id_foreign` (`fk_container_id`),
    KEY `cloud_apps_containers_fk_app_id_foreign` (`fk_app_id`),
    CONSTRAINT `cloud_apps_containers_fk_container_id_foreign` FOREIGN KEY (`fk_container_id`) REFERENCES `$tonicsCloudContainer` (`container_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `cloud_apps_containers_fk_app_id_foreign` FOREIGN KEY (`fk_app_id`) REFERENCES `$tonicsCloudApps` (`app_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS_TO_CONTAINERS);
    }
}