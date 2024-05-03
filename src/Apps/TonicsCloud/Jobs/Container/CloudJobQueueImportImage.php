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

        $client = ContainerController::getIncusClient($serviceInstanceOthers);

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