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