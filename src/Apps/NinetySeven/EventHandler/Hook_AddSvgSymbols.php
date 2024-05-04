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

namespace App\Apps\NinetySeven\EventHandler;

use App\Modules\Core\Boot\InitLoaderMinimal;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class Hook_AddSvgSymbols implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        $helper = helper();
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('in_svg_defs', function (TonicsView $tonicsView) use ($helper) {
            $audioSymbol = '';
            if ($tonicsView->accessArrayWithSeparator('Data.TonicsBeatsTonics_Theme')){
                $audioSymbol = implode('', helper()->getIconSymbols([
                    // Audio Player
                    $helper::SvgSymbol_Shopping_Cart,
                    $helper::SvgSymbol_Cart,
                    $helper::SvgSymbol_Shuffle,
                    $helper::SvgSymbol_Shuffle_On,
                    $helper::SvgSymbol_Shuffle_Off,
                    $helper::SvgSymbol_Repeat_Off,
                    $helper::SvgSymbol_Repeat_On,
                    $helper::SvgSymbol_Step_Backward,
                    $helper::SvgSymbol_Step_Forward,
                    $helper::SvgSymbol_Audio_Play,
                    $helper::SvgSymbol_Music_Playlist,
                    $helper::SvgSymbol_Play_Outline,
                    $helper::SvgSymbol_Audio_Pause,
                    $helper::SvgSymbol_Pause_Outline,
                    $helper::SvgSymbol_Folder,
                    $helper::SvgSymbol_Download,
                    $helper::SvgSymbol_Remove,
                ]));
            }

            $globalMenus = array_values(InitLoaderMinimal::getGlobalVariableData('Menu.SVG_ICONS') ?? []);
            $Menus = [
                    $helper::SvgSymbol_Arrow_Down,
                    $helper::SvgSymbol_Arrow_Up,
                    $helper::SvgSymbol_Filter,

                    // Social Symbol
                    $helper::SvgSymbol_Facebook,
                    $helper::SvgSymbol_Youtube,
                    $helper::SvgSymbol_Pinterest,
                    $helper::SvgSymbol_Twitter,
                    $helper::SvgSymbol_Reddit,
                    $helper::SvgSymbol_Whatsapp,
                    $helper::SvgSymbol_Instagram,
                    $helper::SvgSymbol_Mail
            ];

            foreach ($globalMenus as $globalMenu){
                $Menus[] = $globalMenu;
            }

            return implode('', helper()->getIconSymbols($Menus)) . $audioSymbol;
        });
    }
}