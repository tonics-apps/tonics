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
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueCreateContainer extends AbstractJobInterface implements JobHandlerInterface
{

    use TonicsJobQueueContainerTrait;

    /**
     * Here are the steps, we add the certificate if it is not already added
     * @throws \Exception
     */
    public function handle(): void
    {

        $container = $this->getContainer();
        $containerOthers = json_decode($container->containerOthers);

        $client = $this->getIncusClient();

        if ($this->hasImageHash()){
            $source = [
                "alias" => $this->getImageHash(),
                "type" => "image"
            ];
        } else {
            $source = [
                "protocol" => "simplestreams",
                "alias" => "debian/bullseye/amd64",
                "server" => "https://images.linuxcontainers.org",
                "type" => "image"
            ];
        }

        $client->instances()->create([
            "name" => $this->getContainerUniqueSlugID(),
            "description" => $container->container_description,
            "source" => $source,
            "devices" => $this->getCollatedDevicesOrProfiles($containerOthers)
        ]);

        if ($client->operationIsCreated()){
            $this->updateContainerStatus('Creating Container');
        } else {
            $this->logInfoMessage($client);
            $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
        }

    }


}