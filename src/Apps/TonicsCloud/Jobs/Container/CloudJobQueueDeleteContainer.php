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

use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueContainerTrait;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueDeleteContainer extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueContainerTrait;

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        if ($this->hasContainerUniqueSlugID() === false){
            throw new \Exception("Container Unique Name is Missing");
        }

        $client = $this->getIncusClient();
        $response = $client->instances()->delete($this->getContainerUniqueSlugID());
        if ($client->operationIsCreated()){
            $this->updateContainerStatus('Destroying Container');
            $waitResponse = $client->operations()->wait($response->operation, 25);
            if (isset($waitResponse->metadata->err) && str_contains($waitResponse->metadata->err, "Instance not found")){
                $this->markContainerHasDestroyed();
                return;
            }

            if (isset($waitResponse->metadata->status) && strtoupper($waitResponse->metadata->status) === 'SUCCESS'){
                $this->markContainerHasDestroyed();
                return;
            }
        }

        # Meaning The Image Has Been Deleted
        if ($client->isError()){
            if (str_contains($client->errorMessage(), "Instance not found")){
                $this->markContainerHasDestroyed();
                return;
            }
        }

        $this->logInfoMessage($client);
        
        # An Error Occurred Somewhere, let's re-queue for a retry if we have the opportunity
        $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);

    }
}