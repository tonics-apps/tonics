<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Exception;

class CreateObjectsTable_2020_02_00_001340 extends Migration
{

    /**
     * @throws Exception
     */
    public function up()
    {
        $settingsJSON = UserData::generateAdminJSONSettings();

    # Storing the type on a separate table
    $this->getDB()->run("
    CREATE TABLE IF NOT EXISTS `{$this->tableObjectType()}` (
        `object_type_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        object_type_name VARCHAR(20)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    # Add Post To Object Table
    $this->getDB()->run("INSERT INTO `{$this->tableObjectType()}` (`object_type_name`) VALUES('Post');");

    # BASE TABLE:
    # Unique is on object_id and object_type, this way,
    # we guarantee a user can only have a single type on a subtype at a time
    $this->getDB()->run("
    CREATE TABLE IF NOT EXISTS `{$this->tableObject()}` (
        `object_id`  BIGINT AUTO_INCREMENT PRIMARY KEY,
        `object_type` BIGINT UNSIGNED NOT NULL,
        `field_slugs` longtext COLLATE utf8mb4_unicode_ci DEFAULT '{}' CHECK (json_valid(`field_slugs`)),
        `field_settings` longtext COLLATE utf8mb4_unicode_ci DEFAULT '{}',
        `title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
        `slug` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
        `status` tinyint(4) DEFAULT 0,
        `created_at` timestamp DEFAULT current_timestamp(),
        `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        CONSTRAINT `fk_user_type` 
            FOREIGN KEY (object_type) REFERENCES `{$this->tableObjectType()}` (object_type_id) 
                ON DELETE CASCADE
                ON UPDATE CASCADE,
        UNIQUE KEY `object_id_type_must_unique` (object_id, object_type),
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    # Create an Admin Table
    $this->getDB()->run("
    CREATE TABLE IF NOT EXISTS `{$this->tableAdmin()}` (
        `user_id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        -- 1 is for admin and a check ensures we do not populate the wrong type
        `user_type_id` BIGINT UNSIGNED NOT NULL DEFAULT 1 CHECK ( user_type_id = 1 ),
        `role` varchar(255) NOT NULL,
        `settings` longtext NOT NULL DEFAULT '$settingsJSON' CHECK (json_valid(`settings`)),
        CONSTRAINT `fk_admin_user_type_id` 
            FOREIGN KEY (`user_id`, `user_type_id`) REFERENCES `{$this->tableUser()}` (`user_id`, `user_type`) 
                ON DELETE CASCADE
                ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * @throws Exception
     */
    public function down()
    {
        $this->dropTable($this->tableObject());
    }

    private function tableObject(): string
    {
        return Tables::getTable(Tables::OBJECTS);
    }

    private function tableObjectType(): string
    {
        return Tables::getTable(Tables::OBJECT_TYPE);
    }

    private function tablePost(): string
    {
        return Tables::getTable(Tables::POSTS);
    }

}