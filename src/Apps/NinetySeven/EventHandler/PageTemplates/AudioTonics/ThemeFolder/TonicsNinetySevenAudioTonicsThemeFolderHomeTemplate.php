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

use App\Modules\Page\Events\AbstractClasses\PageTemplateInterface;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsNinetySevenAudioTonicsThemeFolderHomeTemplate implements PageTemplateInterface, HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnPageTemplate $event */
        $event->addTemplate($this);
    }

    public function name(): string
    {
        return 'TonicsNinetySeven_AudioTonics_ThemeFolder_Home_Template';
    }

    /**
     * @throws \Exception
     */
    public function handleTemplate(OnPageTemplate $pageTemplate): void
    {
        $pageTemplate->setViewName('Apps::NinetySeven/Views/Track/AudioTonics/ThemeFolder/root');
        $fieldSettings = $pageTemplate->getFieldSettings();
        $fieldSettings['ThemeFolderHome'] = true;
        $fieldSettings[ThemeFolderViewHandler::TonicsAudioTonicsKey] = true;
        $pageTemplate->setFieldSettings($fieldSettings);
    }
}