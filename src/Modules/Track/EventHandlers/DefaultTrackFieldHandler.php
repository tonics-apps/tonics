<?php

namespace App\Modules\Track\EventHandlers;

use App\Modules\Post\Events\OnPostDefaultField;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DefaultTrackFieldHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnPostDefaultField */
        $event->addDefaultField('track-page')->addDefaultField('seo-settings');
    }
}