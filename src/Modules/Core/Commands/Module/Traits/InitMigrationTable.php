<?php

namespace App\Modules\Core\Commands\Module\Traits;

use App\Modules\Core\Library\Tables;

/**
 * This Trait Should Only Be Used As An Extension of The Migration And DatabaseMigrationAbstract Class, nothing more.
 * Period.
 */
trait InitMigrationTable
{
    /**
     * @throws \Exception
     */
    public function initMigrationTable()
    {
        $tablename = Tables::getTable(Tables::MIGRATIONS);
        db()->run("
CREATE TABLE IF NOT EXISTS `$tablename` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}