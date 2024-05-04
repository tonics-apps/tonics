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

use App\Modules\Comment\Data\CommentData;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreateCommentUserTable_2022_08_27_065428 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
     `comment_usertype_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `comment_usertype_name` varchar(255) NOT NULL,
     PRIMARY KEY (`comment_usertype_id`),
     UNIQUE KEY `comment_usertype_name_unique` (`comment_usertype_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $db->Insert($this->tableName(), [
                ['comment_usertype_name' => CommentData::ADMIN_NAME],
                ['comment_usertype_name' => CommentData::CUSTOMER_NAME]
            ]);
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
        return Tables::getTable(Tables::COMMENT_USER_TYPE);
    }
}