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