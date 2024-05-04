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

class CloudCredits_2023_05_07_174435 extends Migration {

    /**
     * @throws \Exception
     */
    public function up(): void
    {
        $customerTable = Tables::getTable(Tables::CUSTOMERS);
        db(onGetDB: function (TonicsQuery $db) use ($customerTable) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
    `credit_id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `credit_amount` decimal(15, 2) DEFAULT NULL,
    `credit_description` text DEFAULT NULL,
    `others` longtext DEFAULT '{}' CHECK (json_valid(`others`)),
    `fk_customer_id` BIGINT NOT NULL,
    `last_checked` timestamp DEFAULT current_timestamp(),
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    KEY `idx_last_checked` (`last_checked`),
    CONSTRAINT `cloud_credits_fk_customer_id_foreign` FOREIGN KEY (`fk_customer_id`) REFERENCES `$customerTable` (`user_id`) ON UPDATE CASCADE
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
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CREDITS);
    }
}