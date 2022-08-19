<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers\JobTransporter;

use App\Modules\Core\Events\OnAddJobTransporter;
use App\Modules\Core\Library\JobSystem\TransporterInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventDispatcherInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DatabaseJobTransporter implements TransporterInterface, EventDispatcherInterface, HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Database';
    }

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddJobTransporter */
        $event->addJobTransporter($this);
    }

    /**
     * @inheritDoc
     */
    public function enqueue(object $event): void
    {
       // dd($event);
    }

    public function dispatch(object $event): object
    {
        // TODO: Implement dispatch() method.
    }

    /**
     * @inheritDoc
     */
    public function isStatic(): bool
    {
        return false;
    }
}