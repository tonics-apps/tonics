<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Widget\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreateWidgetItemsTable_2022_01_13_212947 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $widgetTable = Tables::getTable(Tables::WIDGETS);
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_widget_id` int(10) unsigned NOT NULL,
  `wgt_id` int(10) unsigned NOT NULL, -- this is the actual wg_id gotten from the js ID, we are not using nestable list for this, so, only wg_id would do
  `wgt_name` varchar(255) NOT NULL,
  `wgt_options` longtext NOT NULL CHECK (json_valid(`wgt_options`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `bt_widget_items_fk_widget_id_foreign` (`fk_widget_id`),
  CONSTRAINT `bt_widget_items_fk_widget_id_foreign` FOREIGN KEY (`fk_widget_id`) REFERENCES `$widgetTable` (`widget_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`wgt_options` is null or json_valid(`wgt_options`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down()
    {
        $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::WIDGET_ITEMS);
    }
}