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

class CloudJobQueueStopContainer extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueContainerTrait;

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        if ($this->hasContainerUniqueSlugID() === false){
            throw new \Exception("Container Unique Name is Missing");
        }

        $client = $this->getIncusClient();
        $response = $client->instances()->changeState($this->getContainerUniqueSlugID(), ["action" => "stop", "timeout" => 10]);
        if ($client->operationIsCreated()){
            $this->updateContainerStatus('Stopping Container');
            $waitResponse = $client->operations()->wait($response->operation, 25);

            if (isset($waitResponse->metadata->err) && str_contains( $waitResponse->metadata->err, 'is already stopped')){
                return;
            }

            if (isset($waitResponse->metadata->status) && strtoupper($waitResponse->metadata->status) === 'SUCCESS'){
                return;
            }
        }

        # Meaning The Image Has Been Deleted
        if ($client->isError()){
            if ($client->errorMessage() === "Instance not found"){
                return;
            }
        }

        if (str_contains($client->errorMessage(), 'is already stopped')) {
            return;
        }

        $this->logInfoMessage($client);
        
        # An Error Occurred Somewhere, let's re-queue for a retry if we have the opportunity
        $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);

    }
}