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

use App\Modules\Core\Library\Tables;
use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsNinetySevenBeatsTonicsThemeFolderTrackCategoryTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_BeatsTonics_ThemeFolder_TrackCategory_Template';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        # For Tracks Category
        $routeParams = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams();
        $uniqueSlugID = $routeParams[0] ?? null;

        $mainTrackData = db()->Select('*')->From(Tables::getTable(Tables::TRACK_CATEGORIES))
            ->WhereEquals('slug_id', $uniqueSlugID)->FetchFirst();

        if (isset($mainTrackData->slug_id)) {
            $isFolder = url()->getHeaderByKey('type') === 'isTonicsNavigation';
            $isSearch = url()->getHeaderByKey('type') === 'isSearch';

            // From API
            if ($isFolder){
                $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/folder_main');
            } elseif ($isSearch){
                $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/folder_search');
            } else {
                $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/root');
            }

            $fieldSettings = $pageTemplate->getFieldSettings();
            $fieldSettingsForMainTrackData = json_decode($mainTrackData->field_settings, true);
            $pageTemplate->getFieldData()->unwrapFieldContent($fieldSettingsForMainTrackData, contentKey: 'track_cat_content');
            $fieldSettings['ThemeFolder'] = true;
            $mainTrackData = [...$fieldSettingsForMainTrackData, ...(array)$mainTrackData];
            $fieldSettings = [...$mainTrackData, ...$fieldSettings];
            $pageTemplate->setFieldSettings($fieldSettings);
        }
    }
}