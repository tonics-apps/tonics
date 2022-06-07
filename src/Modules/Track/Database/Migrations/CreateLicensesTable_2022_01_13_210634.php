<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;
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

        // unique_id should be unique for a single row (it can be the same across different rows)
        $licenseAttrJSON = json_encode([
            ['name' => 'Basic', 'unique_id' => helper()->randomString(), 'price' => 50.00, 'license_contract' => '', 'is_enabled' => true ],
            ['name' => 'Premium', 'unique_id' => helper()->randomString(), 'price' => 100.00, 'license_contract' => '', 'is_enabled' => true ],
            ['name' => 'Unlimited', 'unique_id' => helper()->randomString(), 'price' => 200.00, 'license_contract' => '', 'is_enabled' => true ]
        ], JSON_THROW_ON_ERROR);

        // This table stands as license groups
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `license_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `license_name` varchar(255) NOT NULL,
  `license_slug` varchar(255) NOT NULL,
  `license_status` tinyint(1) NOT NULL DEFAULT 1,
  `license_attr` longtext NOT NULL DEFAULT '$licenseAttrJSON' CHECK (json_valid(`license_attr`)),
  PRIMARY KEY (`license_id`),
  CONSTRAINT `CONSTRAINT_1` CHECK (`license_attr` is null or json_valid(`license_attr`))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Default Insert
        $this->getDB()->run("
INSERT INTO {$this->tableName()}(`license_name`, `license_slug`)
VALUES ('Beat Standard','beat-standard');");
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