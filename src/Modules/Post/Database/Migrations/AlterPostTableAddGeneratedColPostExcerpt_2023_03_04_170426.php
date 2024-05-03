<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Post\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AlterPostTableAddGeneratedColPostExcerpt_2023_03_04_170426 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            /**
             * the NULLIF function is used to convert an empty string to NULL,
             * and then COALESCE is used to return the first non-null value in the list of arguments.
             * So if the $.post_excerpt key in the field_settings JSON object is null or empty,
             * the COALESCE function will return the value of the $.seo_description key instead.
             *
             */
            $db->run(
                "
ALTER TABLE `{$this->tableName()}`
ADD COLUMN post_excerpt VARCHAR(500) GENERATED ALWAYS AS (
  JSON_UNQUOTE(
    COALESCE(
      NULLIF(JSON_EXTRACT(field_settings, '$.post_excerpt'), ''),
      NULLIF(JSON_EXTRACT(field_settings, '$.seo_description'), '')
    )
  )
) STORED AFTER post_title;
");
        });
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("ALTER TABLE `{$this->tableName()}` DROP COLUMN post_excerpt;");
        });
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::POSTS);
    }
}