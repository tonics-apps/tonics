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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class AssetsHookHandler implements HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('in_head_stylesheet', function (TonicsView $tonicsView){
            $ninetySevenCSS = AppConfig::getAppAsset('NinetySeven', 'css/styles.min.css');
            return "<link rel='preload stylesheet' type='text/css' as='style' href='$ninetySevenCSS'>" . "\n";
        });

    }
}