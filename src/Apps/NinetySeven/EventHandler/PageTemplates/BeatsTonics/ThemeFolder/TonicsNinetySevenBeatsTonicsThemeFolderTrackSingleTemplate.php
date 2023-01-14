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

class TonicsNinetySevenBeatsTonicsThemeFolderTrackSingleTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_BeatsTonics_ThemeFolder_TrackSingle_Template';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        $fieldSettings = $pageTemplate->getFieldSettings();
        $fieldSettings['ThemeTrackSingle'] = true;
        $isGetMarker = url()->getHeaderByKey('type') === 'getMarker';
        $isAPI = url()->getHeaderByKey('isAPI') === 'true';

        if ($isAPI){
            $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/track_single');
            if (!$isGetMarker){
                $fieldSettings = [...$fieldSettings, ...ThemeFolderViewHandler::handleTrackSingleFragment()];
            }
        } else {
            $fieldSettings = [...$fieldSettings, ...ThemeFolderViewHandler::handleTrackSingleFragment()];
            $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/BeatsTonics/ThemeFolder/root');
        }
        $pageTemplate->setFieldSettings($fieldSettings);
    }
}