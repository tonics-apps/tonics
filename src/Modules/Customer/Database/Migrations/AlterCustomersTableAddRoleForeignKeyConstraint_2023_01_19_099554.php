<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 */

namespace App\Modules\Customer\Database\Migrations;

use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AlterCustomersTableAddRoleForeignKeyConstraint_2023_01_19_099554 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run("ALTER TABLE `{$this->tableCustomer()}` DROP COLUMN `role`;");
            $db->run("ALTER TABLE `{$this->tableCustomer()}` ADD COLUMN `role` INT AFTER `user_password`");
            $db->run("ALTER TABLE `{$this->tableCustomer()}` ADD FOREIGN KEY (`role`) REFERENCES `{$this->tableRole()}`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT;");
        });
    }

    public function tableCustomer(): string
    {
       return Tables::getTable(Tables::CUSTOMERS);
    }

    public function tableRole(): string
    {
        return Tables::getTable(Tables::ROLES);
    }

    public function down()
    {
        $this->getDB()->run("");
    }
}