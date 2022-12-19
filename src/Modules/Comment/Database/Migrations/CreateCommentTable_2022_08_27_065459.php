<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Comment\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateCommentTable_2022_08_27_065459 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        $commentUserTypeTable = Tables::getTable(Tables::COMMENT_USER_TYPE);
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
     `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
     `fk_comment_usertype_id` int(10) unsigned NOT NULL,
     `comment_id` int(10) unsigned NOT NULL,
     `comment_parent_id` int(10) unsigned DEFAULT NULL,
     `comment_body` text DEFAULT NULL,
     `comment_status` enum('pending','denied','approved') NOT NULL DEFAULT 'pending',
     `comment_others` JSON DEFAULT NULL, -- other info about the comment
     `ip_bin` VARBINARY(16)  DEFAULT NULL, -- ip address
     `ip_to_text` varchar(39) GENERATED ALWAYS AS (INET6_NTOA(ip_bin)) VIRTUAL, -- ip address
     `agent` varchar(255) DEFAULT NULL, -- user agent
     `created_at` timestamp DEFAULT current_timestamp(),
     `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
     PRIMARY KEY (`id`),
     KEY `ip_bin_key` (`ip_bin`),
    CONSTRAINT `comment_fk_comment_usertype_id` FOREIGN KEY (`fk_comment_usertype_id`) REFERENCES `$commentUserTypeTable` (`comment_usertype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

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
        return Tables::getTable(Tables::COMMENTS);
    }
}