<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\BeatsTonics\ThemeFolder;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsNinetySevenBeatsTonicsThemeFolderHomeTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_BeatsTonics_ThemeFolder_Home_Template';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/root');
        /**
         * SELECT *, CONCAT_WS("/", "/track_categories", slug_id, track_cat_slug) as _preview_link, (SELECT COUNT(*) FROM tonics_track_track_categories ttc
        INNER JOIN tonics_tracks t ON ttc.fk_track_id = t.track_id
        WHERE ttc.fk_track_cat_id = tonics_track_categories.track_cat_id) as num_tracks FROM tonics_track_categories WHERE track_cat_parent_id IS NULL;
         */

        $trackTable = Tables::getTable(Tables::TRACKS);
        $trackCategoriesTable = Tables::getTable(Tables::TRACK_CATEGORIES);
        $trackTracksCategoriesTable = Tables::getTable(Tables::TRACK_TRACK_CATEGORIES);
        $genreTable = Tables::getTable(Tables::GENRES);
        $trackGenreTable = Tables::getTable(Tables::TRACK_GENRES);

        $db = db();

        $track_cat_id_col = db()->getTonicsQueryBuilder()->getTables()->getColumn($trackCategoriesTable, 'track_cat_id');
        $data = $db->Select('0 as is_track, track_cat_name as _name, slug_id, CONCAT_WS("/", "/track_categories", slug_id, track_cat_slug) as _link, ')
            ->Select(
                db()->Count()->From("$trackTracksCategoriesTable ttc")->Join("$trackTable t", "ttc.fk_track_id", "t.track_id")
                ->WhereEquals('ttc.fk_track_cat_id', db()->addRawString($track_cat_id_col)))->As('num_tracks')
            ->From($trackCategoriesTable)->WhereNull('track_cat_parent_id')
            ->WhereEquals('track_cat_status', 1)
            ->Where("$trackCategoriesTable.created_at", '<=', helper()->date())
            ->SimplePaginate(AppConfig::getAppPaginationMax());

        $fieldSettings = $pageTemplate->getFieldSettings();
        $fieldSettings['ThemeFolder'] = $data;
        $pageTemplate->setFieldSettings($fieldSettings);
    }
}