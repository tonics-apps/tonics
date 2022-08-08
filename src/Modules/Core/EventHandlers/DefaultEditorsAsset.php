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

use App\Modules\Core\Events\EditorsAsset;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class DefaultEditorsAsset implements HandlerInterface
{
    public function handleEvent(object $event): void
    {
        /** @var $event EditorsAsset */
        $event->addJS('/js/views/field/items/selection-manager/script-combined.js')
            ->addJS('/js/MainTools/Widget/FeaturedImage.js')->addJS('/js/MainTools/Widget/FeaturedLink.js');
    }
}