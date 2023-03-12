<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Apps\NinetySeven\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsNinetysevenDeleteFieldSlugPostHomePage_2023_03_09_134218 extends Migration {

    /**
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        $toDelete = ['app-ninety-seven-post-home-page'];
        db(onGetDB: function (TonicsQuery $db) use ($toDelete) {
            $db->FastDelete($this->getFieldTable(), db()->WhereIn(table()->getColumn($this->getFieldTable(), 'field_slug'), $toDelete));
        });
    }

    public function down()
    {
        // $this->getDB()->run("");
    }

    public function getFieldTable(): string
    {
        return Tables::getTable(Tables::FIELD);
    }
}