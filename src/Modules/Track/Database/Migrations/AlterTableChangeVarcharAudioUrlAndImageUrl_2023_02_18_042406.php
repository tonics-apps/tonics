<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Track\Database\Migrations;

use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AlterTableChangeVarcharAudioUrlAndImageUrl_2023_02_18_042406 extends Migration
{
    use ConsoleColor;

    /**
     * @throws \Exception
     */
    public function up()
    {
        set_time_limit(0);
        db(onGetDB: function (TonicsQuery $db) {
            $db->run("ALTER TABLE `{$this->tableUser()}` MODIFY COLUMN audio_url VARCHAR(500);");
            $db->run("ALTER TABLE `{$this->tableUser()}` MODIFY COLUMN image_url VARCHAR(500);");
        });
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        db(onGetDB: function (TonicsQuery $db) {
            $dbTx = db();
            $dbTx->beginTransaction();
            $db->run("ALTER TABLE `{$this->tableUser()}` MODIFY COLUMN audio_url VARCHAR(255);");
            $db->run("ALTER TABLE `{$this->tableUser()}` MODIFY COLUMN image_url VARCHAR(255);");
            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
        });
    }

    public function tableUser(): string
    {
        return Tables::getTable(Tables::TRACKS);
    }
}