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

namespace App\Modules\Track\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JsonException;

class CreateLicensesTable_2022_01_13_210634 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws JsonException
     * @throws \Exception
     */
    public function up()
    {

        db(onGetDB: function (TonicsQuery $db){
            // unique_id should be unique for a single row (it can be the same across different rows)
            $licenseAttrJSON = json_encode([
                ['name' => 'Basic', 'unique_id' => helper()->randomString(), 'price' => 50.00, 'license_contract' => '', 'is_enabled' => true ],
                ['name' => 'Premium', 'unique_id' => helper()->randomString(), 'price' => 100.00, 'license_contract' => '', 'is_enabled' => true ],
                ['name' => 'Unlimited', 'unique_id' => helper()->randomString(), 'price' => 200.00, 'license_contract' => '', 'is_enabled' => true ]
            ], JSON_THROW_ON_ERROR);

            // This table stands as license groups
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `license_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `license_name` varchar(255) NOT NULL,
  `license_slug` varchar(255) NOT NULL,
  `license_status` tinyint(1) NOT NULL DEFAULT 1,
  `license_attr` longtext NOT NULL DEFAULT '$licenseAttrJSON' CHECK (json_valid(`license_attr`)),
   `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`license_id`),
  CONSTRAINT `CONSTRAINT_1` CHECK (`license_attr` is null or json_valid(`license_attr`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Default Insert
            $db->run("
INSERT INTO {$this->tableName()}(`license_name`, `license_slug`)
VALUES ('Beat Standard','beat-standard');");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down()
    {
        return $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::LICENSES);
    }
}