<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Post\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateCatRelsTable_2022_01_13_213355 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ## This would be the category relationship for the Post table. it is not related to track table
        ## the post_id is the post id and the cat_parent_id is the parent category id of the post
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `catrel_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `cat_parent_id` int(11) NOT NULL,
  PRIMARY KEY (`catrel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::CAT_RELS);
    }
}