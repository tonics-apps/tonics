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

use App\Apps\TonicsCloud\Services\ContainerService;
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

            $db->Q()->insertOnDuplicate($this->tableName(), ContainerService::DEFAULT_PROFILES(), ['container_profile_description', 'others']);
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