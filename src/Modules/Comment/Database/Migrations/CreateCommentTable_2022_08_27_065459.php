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

namespace App\Modules\Comment\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreateCommentTable_2022_08_27_065459 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db) {
            $commentUserTypeTable = Tables::getTable(Tables::COMMENT_USER_TYPE);
            $db->run("
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
        return Tables::getTable(Tables::COMMENTS);
    }
}