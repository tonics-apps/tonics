<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Customer\Database\Migrations;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Exception;

class CreateCustomersTable_2022_01_13_195307 extends Migration {

    /**
     * @throws Exception
     */
    public function up()
    {
        $settingsJSON = UserData::generateCustomerJSONSettings();

    # Add Customer To UserType Table
    $this->getDB()->run("INSERT INTO `{$this->tableUsersType()}` (`user_type_name`) VALUES('Customer');");

    $this->getDB()->run("
    CREATE TABLE IF NOT EXISTS `{$this->tableCustomer()}` (
       `user_id`  BIGINT AUTO_INCREMENT PRIMARY KEY ,
       -- 2 is for customer and a check ensures we do not populate the wrong type
        `user_type_id` BIGINT UNSIGNED NOT NULL DEFAULT 2 CHECK ( user_type_id = 2 ),
      -- `slug_id` bigint(20) DEFAULT NULL,
      `is_guest` tinyint(1) NOT NULL DEFAULT 0,
      `settings` longtext NOT NULL DEFAULT '$settingsJSON' CHECK (json_valid(`settings`)),
          CONSTRAINT `fk_customer_user_type_id` 
            FOREIGN KEY (`user_id`, `user_type_id`) REFERENCES `{$this->tableUser()}` (`user_id`, `user_type`) 
                ON DELETE CASCADE
                ON UPDATE CASCADE
                                
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    }

    /**
     * @throws Exception
     */
    public function down()
    {
        $this->dropTable($this->tableCustomer());
    }

    private function tableCustomer(): string
    {
        return Tables::getTable(Tables::CUSTOMERS);
    }

    private function tableUser(): string
    {
        return Tables::getTable(Tables::USERS);
    }

    private function tableUsersType()
    {
        return Tables::getTable(Tables::USER_TYPE);
    }

}