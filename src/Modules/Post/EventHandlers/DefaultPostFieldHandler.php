<?php

namespace App\Modules\Post\EventHandlers;

use App\Modules\Post\Events\OnPostDefaultField;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DefaultPostFieldHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnPostDefaultField */
        $event->addDefaultField('post-page')->addDefaultField('seo-settings');
    }
}