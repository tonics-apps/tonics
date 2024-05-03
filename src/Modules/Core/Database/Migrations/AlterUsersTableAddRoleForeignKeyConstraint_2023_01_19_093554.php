<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Core\Database\Migrations;

use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AlterUsersTableAddRoleForeignKeyConstraint_2023_01_19_093554 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("ALTER TABLE `{$this->tableUser()}` DROP COLUMN `role`;");
            $db->run("ALTER TABLE `{$this->tableUser()}` ADD COLUMN `role` INT AFTER `user_password`");
            $db->run("ALTER TABLE `{$this->tableUser()}` ADD FOREIGN KEY (`role`) REFERENCES `{$this->tableRole()}`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT;");
        });
    }

    public function tableUser(): string
    {
       return Tables::getTable(Tables::USERS);
    }

    public function tableRole(): string
    {
        return Tables::getTable(Tables::ROLES);
    }

    public function down()
    {
        // $this->getDB()->run("");
    }
}