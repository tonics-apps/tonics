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
use App\Apps\TonicsCloud\Services\ContainerService;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CloudJobQueueImportImage extends AbstractJobInterface implements JobHandlerInterface
{
    use TonicsJobQueueContainerTrait;

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle(): void
    {
        $container = $this->getContainer();
        $containerOthers = json_decode($container->containerOthers);
        $serviceInstanceOthers = json_decode($container->serviceInstanceOthers);

        $client = ContainerService::getIncusClient($serviceInstanceOthers);

        if (isset($this->getDataAsArray()['container_image'])) {
            $image = $this->getImageOthers();
            $imageVersion = $this->getDefaultImageVersion();

            if (!isset($image->images->{$imageVersion}->image_hash)){
                throw new \Exception("Image Hash Property is Missing in ImageData");
            }
            $imageHash = $image->images->{$imageVersion}->image_hash;

            $siteURL = trim(AppConfig::getAppUrl(), '/');
            $imageMirror = $siteURL . route('tonicsCloud.images.download', [$containerOthers->container_image]) . "?token=" . AppConfig::getKey() . "&version=$imageVersion";

            $parameter = [
                'auto_update' => false,
                'aliases' => [
                    ['name' => $imageHash]
                ],
                'source' => [
                    'mode' => 'pull',
                    'type' => 'url',
                    'url' => trim($imageMirror)
                ]
            ];

            $this->updateContainerStatus('Importing Image');
            $client->images()->add($parameter);
            // $waitResponse = $client->operations()->wait($client->getResponse()->operation, 25);
            if ($client->operationIsCreated() === false){
                $this->logInfoMessage($client);
                $err = "An Error Occurred Creating A Container From Image";
                throw new \Exception($err);
            }
            # Else, the Image is creating...
        }

    }
}