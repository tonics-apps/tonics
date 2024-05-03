<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
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