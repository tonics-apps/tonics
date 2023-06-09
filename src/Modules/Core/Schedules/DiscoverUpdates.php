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

use App\Modules\Core\Commands\UpdateMechanism\Updates;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\States\UpdateMechanismState;

class DiscoverUpdates extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{
    use ConsoleColor;

    public function __construct()
    {
        $this->setName('Core_DiscoverUpdates');
        $this->setPriority(Scheduler::PRIORITY_MEDIUM);
        $this->setEvery(Scheduler::everyMinute(30));

        $autoUpdates = new AutoUpdates();
        $autoUpdates->setName('Core_AutoUpdateAfterDiscovering')->setParentObject($this);
        $this->setChains([$autoUpdates]);
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $updateMechanismState = new UpdateMechanismState(types: ['module', 'app'], discoveredFrom: UpdateMechanismState::DiscoveredFromConsole);
        $updateMechanismState->runStates(false);
        if ($updateMechanismState->getStateResult() === SimpleState::DONE){
            $this->successMessage('Apps and Modules Discovery Update Check Done');
        }
    }
}