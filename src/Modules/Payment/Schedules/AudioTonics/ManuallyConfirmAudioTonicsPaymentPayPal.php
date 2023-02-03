<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Schedules\AudioTonics;

use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;

class ManuallyConfirmAudioTonicsPaymentPayPal extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{

    public function __construct()
    {
        $this->setName('Core_ManuallyConfirmAudioTonicsPaymentPayPal');
        $this->setPriority(Scheduler::PRIORITY_LOW);
        $this->setEvery(Scheduler::everyMinute(5));
    }

    public function handle(): void
    {
        // TODO: Implement handle() method.
    }
}