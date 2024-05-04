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

class UpdatePageTemplateInPageTable_2023_03_08_084922 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        db(onGetDB: function (TonicsQuery $db){
            $db->run(<<<SQL
UPDATE `{$this->tableName()}`
SET `page_template` = 
    CASE 
        WHEN `page_template` = 'TonicsNinetySeven_BeatsTonics_ThemeFolder_Home_Template' 
            THEN 'TonicsNinetySeven_AudioTonics_ThemeFolder_Home_Template'
        WHEN `page_template` = 'TonicsNinetySeven_BeatsTonics_ThemeFolder_TrackCategory_Template' 
            THEN 'TonicsNinetySeven_AudioTonics_ThemeFolder_TrackCategory_Template'
        WHEN `page_template` = 'TonicsNinetySeven_BeatsTonics_ThemeFolder_TrackSingle_Template' 
            THEN 'TonicsNinetySeven_AudioTonics_ThemeFolder_TrackSingle_Template'
        WHEN `page_template` = 'TonicsNinetySeven_HomePageTemplate' 
            THEN 'TonicsNinetySeven_WriTonics_PostPageTemplate'
        ELSE `page_template`
    END
SQL);
        });
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::PAGES);
    }

    public function down()
    {
        // $this->getDB()->run("");
    }
}