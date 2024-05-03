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

class CloudJobQueueImageImported extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueContainerTrait;

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $client = $this->getIncusClient();
        $client->images()->info($this->getImageHash());
        $this->updateContainerStatus('Verifying Image Importation');
        # If it is not success, let's queue for a retry
        if ($client->isSuccess() === false){
            $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
            $this->logInfoMessage($client);
        }

        # Else...If we get here, it would be set to processed by default
    }
}