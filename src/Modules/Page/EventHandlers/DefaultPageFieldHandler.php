<?php

namespace App\Modules\Page\EventHandlers;

use App\Modules\Post\Events\OnPostDefaultField;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DefaultPageFieldHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnPostDefaultField */
        $event->addDefaultField('default-page-field');
    }
}