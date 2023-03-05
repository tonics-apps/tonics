<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreatePostToCategoriesTable_2022_01_13_213416 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {

        db(onGetDB: function (TonicsQuery $db){
            ## Many To Many Rel --- A Post May Have Many Categories and Likewise,
            ##  A Category Can Belong To Many Posts

            $postTable = Tables::getTable(Tables::POSTS);
            $catTable = Tables::getTable(Tables::CATEGORIES);

            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fk_cat_id` int(10) unsigned NOT NULL,
  `fk_post_id` int(10) unsigned NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `bt_post_categories_fk_cat_id_foreign` (`fk_cat_id`),
  KEY `bt_post_categories_fk_post_id_foreign` (`fk_post_id`),
  CONSTRAINT `bt_post_categories_fk_cat_id_foreign` FOREIGN KEY (`fk_cat_id`) REFERENCES `$catTable` (`cat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bt_post_categories_fk_post_id_foreign` FOREIGN KEY (`fk_post_id`) REFERENCES `$postTable` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
        return Tables::getTable(Tables::POST_CATEGORIES);
    }
}