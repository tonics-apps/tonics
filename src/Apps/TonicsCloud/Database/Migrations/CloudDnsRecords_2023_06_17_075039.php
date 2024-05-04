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

class CloudDnsRecords_2023_06_17_075039 extends Migration {

    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $customerTable = Tables::getTable(Tables::CUSTOMERS);
            $providerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    `dns_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `slug_id` UUID NOT NULL DEFAULT uuid(),
    `dns_domain` varchar(255) NOT NULL,
    `dns_status_msg` varchar(255) DEFAULT 'Okay',
    `fk_provider_id` int(10) unsigned DEFAULT NULL,
    `fk_customer_id` BIGINT NOT NULL,
    `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FULLTEXT KEY `dns_domain_fulltext_index` (`dns_domain`),
    UNIQUE idx_domain (dns_domain),
    CONSTRAINT `dns_record_fk_customer_id_foreign` FOREIGN KEY (`fk_customer_id`) REFERENCES `$customerTable` (`user_id`) ON UPDATE CASCADE,
    CONSTRAINT `dns_record_fk_provider_id_foreign` FOREIGN KEY (`fk_provider_id`) REFERENCES `$providerTable` (`provider_id`) ON UPDATE CASCADE
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
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_DNS);
    }
}