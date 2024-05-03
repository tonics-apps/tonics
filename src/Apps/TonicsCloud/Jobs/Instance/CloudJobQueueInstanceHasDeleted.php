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