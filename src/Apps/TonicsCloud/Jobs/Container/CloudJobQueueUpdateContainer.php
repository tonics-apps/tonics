<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\Container;


use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueContainerTrait;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class CloudJobQueueUpdateContainer extends AbstractJobInterface implements JobHandlerInterface
{

    use TonicsJobQueueContainerTrait;

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $container = $this->getContainer();
        $containerOthers = json_decode($container->containerOthers);

        $client = $this->getIncusClient();
        $instanceInfo = $client->instances()->info($this->getContainerUniqueSlugID());
        if ($client->isSuccess()){
            $update = [
                "architecture" => $instanceInfo->metadata->architecture,
                "description" => $container->container_description,
                "devices" => $this->getCollatedDevicesOrProfiles($containerOthers),
                "profiles" => ['default'],
                "config" => (array)$instanceInfo->metadata->config,
                "ephemeral" => false
            ];

            $response = $client->instances()->update($this->getContainerUniqueSlugID(), $update);
            $waitResponse = $client->operations()->wait($response->operation, 30);
            $this->updateContainerStatus('Updating Container');
            if (isset($waitResponse->metadata->status) && strtoupper($waitResponse->metadata->status) === 'SUCCESS'){
                return;
            }
        }

        $this->logInfoMessage($client);

        # An Error Occurred Somewhere, let's re-queue for a retry if we have the opportunity
        $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);

    }


}