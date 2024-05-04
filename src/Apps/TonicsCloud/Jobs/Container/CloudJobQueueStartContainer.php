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

namespace App\Apps\TonicsCloud\Jobs\Container;

use App\Apps\TonicsCloud\Jobs\Container\Traits\TonicsJobQueueContainerTrait;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueStartContainer extends AbstractJobInterface implements JobHandlerInterface
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
        $response = $client->instances()->changeState($this->getContainerUniqueSlugID(), ["action" => "start", "timeout" => 10]);
        if ($client->operationIsCreated()){
            $this->updateContainerStatus('Starting Container');
            $waitResponse = $client->operations()->wait($response->operation, 25);

            if (isset($waitResponse->metadata->err) && $waitResponse->metadata->err === 'The instance is already running'){
                return;
            }

            if (isset($waitResponse->metadata->status) && strtoupper($waitResponse->metadata->status) === 'SUCCESS'){
                return;
            }
        }


        $this->logInfoMessage($client);
        
        # An Error Occurred Somewhere, let's re-queue for a retry if we have the opportunity
        $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);

    }
}