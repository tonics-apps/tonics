<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Schedules;

use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;

class DiscoverUpdates extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{
    use ConsoleColor;

    public function __construct()
    {
        $this->setName('Core_DiscoverUpdates');
        $this->setPriority(Scheduler::PRIORITY_MEDIUM);
        $this->setEvery(Scheduler::everyMinute(10));

        $autoUpdates = new AutoUpdates();
        $autoUpdates->setName('Core_AutoUpdateAfterDiscovering');
        $autoUpdates->setParent($this);
        $this->setChains([$autoUpdates]);
    }

    public function handle(): void
    {
        $this->infoMessage($this->getName());
    }
}