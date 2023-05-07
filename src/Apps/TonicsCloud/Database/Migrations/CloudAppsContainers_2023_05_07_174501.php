<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\TonicsCloud\Database\Migrations;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
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
    `fk_container_id` int(10) unsigned NOT NULL,
    `fk_app_id` int(10) unsigned NOT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
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