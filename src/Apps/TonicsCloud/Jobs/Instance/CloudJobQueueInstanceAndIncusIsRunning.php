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

use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Jobs\Instance\Traits\TonicsJobQueueInstanceTrait;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use Exception;
use Throwable;

class CloudJobQueueInstanceAndIncusIsRunning extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueInstanceTrait;

    /**
     * @return void
     * @throws Exception
     * @throws Throwable
     */
    public function handle(): void
    {
        $handler = $this->getHandler();
        $running = $handler->isStatus($this->getDataAsArray(), CloudServerInterface::STATUS_RUNNING);

        if ($running === false){
           $instanceStatus = $handler->instanceStatus($this->getDataAsArray());

           if ($instanceStatus !== null){
               $this->updateContainerStatus($instanceStatus);
           } else {
               # Here the status is null, meaning the instance doesn't even exist, at least, we consider it that way
               $this->setJobStatusAfterJobHandled(Job::JobStatus_Failed);
               return;
           }

           # Requeue until we can confirm job is running
            $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
        } else {

            # At this point, we can confirm that the instance is running, now, let's confirm that incus is ready and can connect to the server
            $this->updateContainerStatus('Preparing Instance');

            try {

                $serviceInstanceOthers = $this->getServiceInstanceOthers($this->getServiceInstance());
                $client = ContainerController::getIncusClient($serviceInstanceOthers);
                $client->setTimeout(100)->server()->environment();

            } catch (Exception $exception) {

/*                if (str_contains($exception->getMessage(), "Couldn't connect to server")) {
                    # Requeue until we can confirm incus can connect to server
                    $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
                }*/

                # Requeue until we can confirm incus can connect to server
                $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
                return;

            }

            $this->updateContainerStatus('Running');

        }

    }

    public function getRetryAfter(): ?int
    {
        return Scheduler::everySecond(10);
    }
}