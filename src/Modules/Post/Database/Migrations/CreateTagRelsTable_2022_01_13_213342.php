<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Post\Database\Migrations;

use App\Library\Migration;
use App\Library\Tables;

class CreateTagRelsTable_2022_01_13_213342 extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ## This would be the tag relationship for the Post table. it is not related to track table
        ## It also contains foreign keys

        $postTable = Tables::getTable(Tables::POSTS);
        $tagTable = Tables::getTable(Tables::TAGS);

        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `fk_post_id` int(10) unsigned NOT NULL,
  `fk_tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fk_post_id`,`fk_tag_id`),
  KEY `bt_tag_rels_fk_tag_id_foreign` (`fk_tag_id`),
  CONSTRAINT `bt_tag_rels_fk_post_id_foreign` FOREIGN KEY (`fk_post_id`) REFERENCES `$postTable` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bt_tag_rels_fk_tag_id_foreign` FOREIGN KEY (`fk_tag_id`) REFERENCES `$tagTable` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
        return Tables::getTable(Tables::TAG_RELS);
    }
}