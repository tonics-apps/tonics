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

class CloudJobQueueCreateContainer extends AbstractJobInterface implements JobHandlerInterface
{

    use TonicsJobQueueContainerTrait;

    /**
     * Here are the steps, we add the certificate if it is not already added
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle (): void
    {

        $container = $this->getContainer();
        $containerOthers = json_decode($container->containerOthers);

        $client = $this->getIncusClient();

        if ($this->hasImageHash()) {
            $source = [
                "alias" => $this->getImageHash(),
                "type"  => "image",
            ];
        } else {
            $source = [
                "protocol" => "simplestreams",
                "alias"    => "debian/bookworm/amd64",
                "server"   => "https://images.linuxcontainers.org",
                "type"     => "image",
            ];
        }

        $client->instances()->create([
            "name"        => $this->getContainerUniqueSlugID(),
            "description" => $container->container_description,
            "source"      => $source,
            "devices"     => $this->getCollatedDevicesOrProfiles($containerOthers),
        ]);

        if ($client->operationIsCreated()) {
            $this->updateContainerStatus('Creating Container');
        } else {
            $this->logInfoMessage($client);
            $this->setJobStatusAfterJobHandled(Job::JobStatus_Queued);
        }

    }
}