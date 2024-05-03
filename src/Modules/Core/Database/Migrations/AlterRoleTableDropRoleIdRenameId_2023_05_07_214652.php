<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AlterRoleTableDropRoleIdRenameId_2023_05_07_214652 extends Migration
{

    /**
     * @throws \Exception
     */
    public function up()
    {

        db(onGetDB: function (TonicsQuery $db) {
            $db->run("
            ALTER TABLE `{$this->tableRole()}`
            DROP COLUMN `role_id`,
            RENAME COLUMN `id` TO `role_id`,
            DROP INDEX `role_id_unique`");

            Roles::UPDATE_DEFAULT_ROLES();
        });

    }

    public function down()
    {
        // $this->getDB()->run("");
    }


    public function tableRole(): string
    {
        return Tables::getTable(Tables::ROLES);
    }

}