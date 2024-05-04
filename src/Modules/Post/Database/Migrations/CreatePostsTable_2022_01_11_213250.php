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

namespace App\Modules\Post\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreatePostsTable_2022_01_11_213250 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */

    /*
     * We have an up and a down function
     */
    public function up() {

        db(onGetDB: function (TonicsQuery $db){
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug_id` char(16) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `post_title` varchar(255) NOT NULL,
  `post_slug` varchar(255) NOT NULL,
  `post_status` tinyint(4) DEFAULT 0,
  `field_settings` longtext DEFAULT '{}' CHECK (json_valid(`field_settings`)),
  `created_at` timestamp DEFAULT current_timestamp() ,
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`post_id`),
  UNIQUE KEY (`slug_id`),
  UNIQUE KEY `posts_post_slug_unique` (`post_slug`),
  FULLTEXT KEY `post_fulltext_index` (`post_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
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
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::POSTS);
    }
}