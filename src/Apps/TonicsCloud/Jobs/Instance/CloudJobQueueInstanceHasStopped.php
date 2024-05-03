<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\Instance;

use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Jobs\Instance\Traits\TonicsJobQueueInstanceTrait;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;

class CloudJobQueueInstanceHasStopped extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueInstanceTrait;

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $handler = $this->getHandler();
        $stopped = $handler->isStatus($this->getDataAsArray(), CloudServerInterface::STATUS_STOPPED);

        if ($stopped === false){
           $instanceStatus = $handler->instanceStatus($this->getDataAsArray());
           if ($instanceStatus !== null){
               $this->updateContainerStatus($instanceStatus);
           }

           # Requeue until we can confirm job has stopped
            $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
        } else {
            $this->updateContainerStatus('Offline');
        }

    }

    public function getRetryAfter(): ?int
    {
        return Scheduler::everySecond(10);
    }
}