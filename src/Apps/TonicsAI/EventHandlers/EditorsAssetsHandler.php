<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsAI\EventHandlers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\EditorsAsset;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class EditorsAssetsHandler implements HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event EditorsAsset */
        $tocAsset = AppConfig::getAppAsset('TonicsAI', 'js/main.js');
        $event->addJS($tocAsset);
    }
}