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

namespace App\Modules\Page\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CreatePagesTable_2022_01_13_202136 extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {

        db(onGetDB: function (TonicsQuery $db) {
            $db->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `field_ids` longtext DEFAULT '{}' CHECK (json_valid(`field_ids`)),
  `page_template` varchar(255) DEFAULT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_slug` varchar(255) NOT NULL,
  `page_status` tinyint(4) NOT NULL DEFAULT 1,  
  `field_settings` longtext DEFAULT '{}' CHECK (json_valid(`field_settings`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `pages_page_slug_unique` (`page_slug`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $db->insertOnDuplicate($this->tableName(), self::defaultPages(), ['field_ids']);
        });

    }

    public static function defaultPages(): array
    {
        return [
            ['field_ids' => '["default-page-field"]', 'page_title' => 'HomePage', 'page_slug' => '/', 'page_template' => 'TonicsNinetySeven_HomePageTemplate'],
            ['field_ids' => '["default-page-field","seo-settings","all-posts-page"]', 'page_title' => 'Posts', 'page_slug' => '/posts', 'page_template' => 'TonicsNinetySeven_HomePageTemplate'],
            ['field_ids' => '["default-page-field","all-categories-page"]', 'page_title' => 'Categories', 'page_slug' => '/categories', 'page_template' => ''],

            ['field_ids' => '["default-page-field"]', 'page_title' => 'Tracks Page', 'page_slug' => '/tracks', 'page_template' => 'TonicsNinetySeven_BeatsTonics_ThemeFolder_Home_Template'],
            ['field_ids' => '["default-page-field"]', 'page_title' => 'Track Category Pag', 'page_slug' => "/track_categories/:slug-id/:slug", 'page_template' => 'TonicsNinetySeven_BeatsTonics_ThemeFolder_TrackCategory_Template'],
            ['field_ids' => '["default-page-field"]', 'page_title' => 'Track Single Page', 'page_slug' => '/tracks/:slug-id/:slug', 'page_template' => 'TonicsNinetySeven_BeatsTonics_ThemeFolder_TrackSingle_Template'],

            ['field_ids' => '["default-page-field"]', 'page_title' => 'Genre Page', 'page_slug' => '/genres', 'page_template' => ''],
            ['field_ids' => '["default-page-field"]', 'page_title' => 'Artist Page', 'page_slug' => '/artists', 'page_template' => '']
        ];
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
        return Tables::getTable(Tables::PAGES);
    }
}