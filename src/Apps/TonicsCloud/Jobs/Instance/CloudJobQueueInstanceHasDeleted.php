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

use App\Apps\TonicsCloud\Jobs\Instance\Traits\TonicsJobQueueInstanceTrait;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueueInstanceHasDeleted extends AbstractJobInterface implements JobHandlerInterface
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
        $deleted = false;

        try {
            $info = $handler->instance($this->getDataAsArray());
            if (isset($info['status'])) {
                $this->updateContainerStatus($info['status']);
            } elseif (empty($info)) {
                $deleted = true;
                $this->deleteServer();
            }
        } catch (\Exception $exception){
            if ($exception->getMessage() === 'Not Found' || $exception->getCode() === 404) {
                $deleted = true;
                $this->deleteServer();
            }
        }

        if ($deleted === false){
            # Requeue until we can confirm job has stopped
            $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
        }

    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function deleteServer(): void
    {
        db(onGetDB: function (TonicsQuery $db) use (&$deleted){
            $db->beginTransaction();

            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $endTime = date('Y-m-d H:i:s');
            $db->FastUpdate($serviceInstanceTable, ['end_time' => $endTime, 'service_instance_status' => 'Destroyed'], db()->Q()->WhereEquals('service_instance_id', $this->getServiceInstanceID())
                ->WhereNull('end_time'));

            # Destroy Container Associated With Instance
            $containerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
            $db->Update($containerTable)
                ->Set('end_time', date('Y-m-d H:i:s'))
                ->Set('container_status', "Destroyed")
                ->WhereEquals('service_instance_id', $this->getServiceInstanceID())
                ->Exec();

            $db->commit();
        });
    }

    public function getRetryAfter(): ?int
    {
        return Scheduler::everySecond(10);
    }
}