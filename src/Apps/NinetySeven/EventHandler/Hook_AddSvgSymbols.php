<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\EventHandler;

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
                ]));
            }

            return implode('', helper()->getIconSymbols([
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
                $helper::SvgSymbol_Mail,
            ])) . $audioSymbol;
        });
    }
}