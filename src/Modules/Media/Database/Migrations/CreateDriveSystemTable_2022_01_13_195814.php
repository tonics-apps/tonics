<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Media\Database\Migrations;

use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Exception;

class CreateDriveSystemTable_2022_01_13_195814 extends Migration
{

    /**
     * @throws Exception
     */
    public function up()
    {
        $properties = json_encode([
            'ext' => null,
            'filename' => null,
            'size' => null,
            "time_created" => null,
            "time_modified" => null,
        ]);

        $security = json_encode([
            "lock" => false, // the below would be in effect when lock is set to true
            "password" => null,
        ]);

        $this->getDB()->run("
        CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `drive_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `drive_parent_id` bigint(20) unsigned NULL,
  -- The drive_unique_id must be unique, this way, even if filename changes it would still work
  `drive_unique_id` char(64) NOT NULL,
  `drive_name` varchar(255) NOT NULL DEFAULT 'local',
  `filename` varchar(255) NOT NULL,
  -- `filepath` varchar(255) AS (a mod 10) VIRTUAL,
  `type` enum('file','directory') NOT NULL DEFAULT 'file',
    -- This would determine if it should be removed from the actual drive system
    -- bin --- meaning stored in the recycle bin, so, it can be retrieve later (perhaps we should give user an option to set when bin should be cleaned)
    -- bin only works for local drive
  `status` enum('live', 'bin') NOT NULL DEFAULT 'live',
  `properties` longtext CHARACTER SET utf8mb4 NOT NULL DEFAULT '$properties' CHECK (json_valid(`properties`)),
  `security` longtext CHARACTER SET utf8mb4 NOT NULL DEFAULT '$security' CHECK (json_valid(`security`)),
   PRIMARY KEY (`drive_id`),
   UNIQUE KEY (`drive_unique_id`),
  -- Meaning you can only have a unique filename per directory
  -- UNIQUE KEY (`drive_parent_id`, `filename`),
  INDEX filename_index (`filename`),
  CONSTRAINT `fk_driveparent_to_driveid`
    FOREIGN KEY (drive_parent_id) REFERENCES {$this->tableName()} (drive_id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $relPath = helper()->getDriveSystemRelativeSignature(DriveConfig::getPrivatePath(), DriveConfig::getUploadsPath());
        $initFolder = new \SplFileInfo(DriveConfig::getUploadsPath());
        $rootDir[] = [
            "drive_id" => 1,
            "drive_parent_id" => null,
            "drive_unique_id" => hash('sha256', $relPath . random_int(0000000, PHP_INT_MAX)),
            "drive_name" => 'local',
            "type" => "directory",
            'filename' => $initFolder->getFilename(),
            "properties" => json_encode([
                'ext' => null,
                'filename' => $initFolder->getFilename(),
                'size' => $initFolder->getSize(),
                "time_created" => $initFolder->getCTime(),
                "time_modified" => $initFolder->getMTime()
            ]),
            "security" => json_encode([
                "lock" => false,
                "password" => 1 . random_int(0000000, PHP_INT_MAX),
            ])
        ];

        $this->getDB()->insertOnDuplicate(
            table: $this->tableName(), data: $rootDir, update: ['drive_id', 'filename', 'properties'], chunkInsertRate: 1
        );

    }

    /**
     * @throws Exception
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::DRIVE_SYSTEM);
    }
}