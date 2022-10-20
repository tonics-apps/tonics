<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\EditorsAsset;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class DefaultEditorsAsset implements HandlerInterface
{
    public function handleEvent(object $event): void
    {
        /** @var $event EditorsAsset */
        $event
            ->addJS(AppConfig::getModuleAsset('Core', '/js/views/field/items/selection-manager/script-combined.js'))
            ->addJS(AppConfig::getModuleAsset('Core', '/js/views/field/native/script.js'))
            ->addJS(AppConfig::getModuleAsset('Core', '/js/tools/Widget/FeaturedImage.js'))
            ->addJS(AppConfig::getModuleAsset('Core', '/js/tools/Widget/FeaturedLink.js'));
    }
}