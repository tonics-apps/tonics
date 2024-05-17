<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Apps\TonicsCloud\Jobs\Instance;

use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Jobs\Instance\Traits\TonicsJobQueueInstanceTrait;
use App\Apps\TonicsCloud\Services\ContainerService;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use Exception;
use Throwable;

class CloudJobQueueInstanceAndIncusIsRunning extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueInstanceTrait;

    public function __construct()
    {
        $this->setMaxAttempts(50);
    }

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
                $client = ContainerService::getIncusClient($serviceInstanceOthers);
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