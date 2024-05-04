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