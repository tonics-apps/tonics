<?php

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