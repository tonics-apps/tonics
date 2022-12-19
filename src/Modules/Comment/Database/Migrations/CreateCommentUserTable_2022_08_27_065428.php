<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Comment\Database\Migrations;

use App\Modules\Comment\Data\CommentData;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;

class CreateCommentUserTable_2022_08_27_065428 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
     `comment_usertype_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `comment_usertype_name` varchar(255) NOT NULL,
     PRIMARY KEY (`comment_usertype_id`),
     UNIQUE KEY `comment_usertype_name_unique` (`comment_usertype_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->getDB()->Insert($this->tableName(), [
            ['comment_usertype_name' => CommentData::ADMIN_NAME],
            ['comment_usertype_name' => CommentData::CUSTOMER_NAME]
        ]);
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