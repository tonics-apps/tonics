<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands\Module\Traits;

use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

/**
 * This Trait Should Only Be Used As An Extension of The Migration And DatabaseMigrationAbstract Class, nothing more.
 * Period.
 */
trait InitMigrationTable
{
    /**
     * @throws \Exception
     */
    public function initMigrationTable(): void
    {
        $tablename = Tables::getTable(Tables::MIGRATIONS);
        db(onGetDB: function (TonicsQuery $db) use ($tablename) {
            $db->run("
CREATE TABLE IF NOT EXISTS `$tablename` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        });

    }
}