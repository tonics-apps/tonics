<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler\PageTemplates\AudioTonics\ThemeFolder;

use App\Modules\Core\Library\Tables;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class TonicsNinetySevenAudioTonicsThemeFolderTrackCategoryTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_AudioTonics_ThemeFolder_TrackCategory_Template';
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        $mainTrackData = null;

        db(onGetDB: function (TonicsQuery $db) use (&$mainTrackData){
            # For Tracks Category
            $routeParams = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
            $uniqueSlugID = $routeParams[0] ?? null;

            $mainTrackData = $db->Select('*')->From(Tables::getTable(Tables::TRACK_CATEGORIES))
                ->WhereEquals('slug_id', $uniqueSlugID)->FetchFirst();
        });

        if (isset($mainTrackData->slug_id)) {
            $isFolder = url()->getHeaderByKey('type') === 'isTonicsNavigation';
            $isSearch = url()->getHeaderByKey('type') === 'isSearch';

            // From API
            if ($isFolder){
                $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/AudioTonics/ThemeFolder/folder_main');
            } elseif ($isSearch){
                $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/AudioTonics/ThemeFolder/folder_search');
            } else {
                $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/AudioTonics/ThemeFolder/root');
            }

            $fieldSettings = $pageTemplate->getFieldSettings();
            $fieldSettingsForMainTrackData = json_decode($mainTrackData->field_settings, true);
            $pageTemplate->getFieldData()->unwrapFieldContent($fieldSettingsForMainTrackData, contentKey: 'track_cat_content');
            $fieldSettings['ThemeFolder'] = true;
            $fieldSettings[ThemeFolderViewHandler::TonicsAudioTonicsKey] = true;
            $mainTrackData = [...$fieldSettingsForMainTrackData, ...(array)$mainTrackData];
            $fieldSettings = [...$mainTrackData, ...$fieldSettings];
            if (isset($fieldSettings['track_cat_parent_id'])){
                $fieldSettings['categories'][] = array_reverse(ThemeFolderViewHandler::getTrackCategoryParents($fieldSettings['track_cat_parent_id']));
            }
            $pageTemplate->setFieldSettings($fieldSettings);
        }
    }
}