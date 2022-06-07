<?php

namespace App\Modules\Post\EventHandlers;

use App\Modules\Post\Events\OnPostCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleUpdatePostToCategoryMapping implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /**
         * @var OnPostCreate $event
         */
    }
}