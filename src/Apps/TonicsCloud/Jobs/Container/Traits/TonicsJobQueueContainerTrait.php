<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\Container\Traits;

use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Library\Incus\Client;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

trait TonicsJobQueueContainerTrait
{
    private mixed $container = null;
    private mixed $image = null;
    private mixed $imageOthers = null;

    /**
     * @return mixed|string
     */
    public function getContainerID(): mixed
    {
        return $this->getDataAsArray()['container_id'] ?? '';
    }

    /**
     * @param Client $client
     * @return void
     * @throws \Exception
     */
    public function logInfoMessage(Client $client): void
    {
        if ($client->isError()){
            $this->setJobInfoMessage($client->errorMessage());
            $this->infoMessage($client->errorMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function getContainer()
    {
        if (!empty($this->container)){
            return $this->container;
        }

        $containerID = $this->getContainerID() ?? $this->getContainerUniqueSlugID();
        $col = (!empty($this->getContainerID())) ? 'container_id' : 'slug_id';
        $container = ContainerController::getContainer($containerID, false, $col);
        if (empty($container)){
            throw new \Exception("An Error Occurred While Trying To Get Container For Creation");
        }

        $this->container = $container;
        return $container;
    }

    /**
     * @return mixed|null
     */
    public function getImage(): mixed
    {
        if (!empty($this->image)){
            return $this->image;
        }

        if (isset($this->getDataAsArray()['container_image'])) {
            $image = $this->getDataAsArray()['container_image'];
            $this->image = $image;
        }

        return $this->image;
    }

    /**
     * @return mixed|null
     */
    public function getImageOthers(): mixed
    {
        if (!empty($this->imageOthers)){
            return $this->imageOthers;
        }

        $image = $this->getImage();
        if ($image) {
            $imageOthers = json_decode($image->others);
            $this->imageOthers = $imageOthers;
        }

        return $this->imageOthers;
    }

    public function getImageVersion()
    {
        return $this->getDefaultImageVersion();
    }

    public function hasImageHash(): bool
    {
        return isset($this->getImageOthers()->images->{$this->getImageVersion()}->image_hash);
    }

    /**
     * @param string $statusMsg
     * @return void
     * @throws \Exception
     */
    public function updateContainerStatus(string $statusMsg): void
    {
        if ($this->canUpdateStatus()){
            db(onGetDB: function (TonicsQuery $db) use ($statusMsg) {
                $containerID = $this->getContainerID();
                $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
                $db->Update($table)
                    ->Set('container_status', $statusMsg)
                    ->WhereNull("end_time")
                    ->WhereEquals('container_id', $containerID)
                    ->Exec();
            });
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function markContainerHasDestroyed(): void
    {
        db( onGetDB: function (TonicsQuery $db) {
            $containerID = $this->getContainerID();
            if ($containerID){
                $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
                $db->Update($table)
                    ->Set('end_time', date('Y-m-d H:i:s'))
                    ->Set('container_status', "Destroyed")
                    ->WhereEquals('container_id', $containerID)
                    ->Exec();
            }
        });
    }

    public function hasContainerUniqueSlugID(): bool
    {
        return isset($this->getDataAsArray()['container_unique_slug_id']);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getContainerUniqueSlugID(): string
    {
        $slugID = ContainerController::getIncusContainerName($this->getDataAsArray()['container_unique_slug_id']) ?? '';
        if (empty($slugID)){
            $data = ContainerController::getContainer($this->getContainerID(), false);
            return $data?->slug_id;
        } else {
            return $slugID;
        }
    }

    /**
     * If we can find the `update_status` key, then by default, we assume true,
     * otherwise, we use the boolean in the update_status value
     * @return bool
     */
    public function canUpdateStatus(): bool
    {
        $updateStatus = $this->getDataAsArray()['update_status'] ?? '';
        if (empty($updateStatus)){
            return true;
        }

        return $updateStatus;
    }

    public function getDefaultImageVersion()
    {
        $defaultImageVersion = '';

        if (isset($this->getDataAsArray()['container_image'])) {

            $image = $this->getDataAsArray()['container_image'];
            if ($image){
                $image = json_decode($image->others);
            }

            $defaultImageVersion = $this->getDataAsObject()->image_version;

            # Get the properties of the object
            $properties = get_object_vars($image->images);
            # Get the first property key
            $firstPropertyKey = array_key_first($properties);
            # We default to the first version if nothing is set
            if (empty($defaultImageVersion) && isset($firstPropertyKey)) {
                $defaultImageVersion = $firstPropertyKey;
            }
        }

        return $defaultImageVersion;

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getImageHash(): mixed
    {
        $imageVersion = $this->getDefaultImageVersion();
        if (!isset($this->getImageOthers()->images->{$imageVersion}->image_hash)) {
            throw new \Exception("Image Hash Property is Missing in ImageData");
        }

        return $this->getImageOthers()->images->{$imageVersion}->image_hash;
    }

    /**
     * @throws \Exception
     */
    public function getCollatedDevicesOrProfiles(\stdClass $containerOthers): array
    {
        $devices = [
            "root" => [
                "type" => "disk",
                "pool" => "default",
                "path" => "/",
            ]
        ];

        if (isset($containerOthers->container_profiles) && is_array($containerOthers->container_profiles)){
            $profiles = $this->getProfiles($containerOthers->container_profiles);
            if ($profiles){
                foreach ($profiles as $profile){
                    $profileOthers = json_decode($profile->others, true);
                    $devices = [...$devices, ...$profileOthers['devices']];
                }
            }
        }

        return $devices;
    }

    /**
     * @param array $profileIDS
     * @return array|bool|null
     * @throws \Exception
     */
    public function getProfiles(array $profileIDS): bool|array|null
    {
        return ContainerController::getProfiles($profileIDS);
    }

    /**
     * @throws \Exception
     */
    public function getIncusClient(): Client
    {
        $serviceInstanceOthers = json_decode($this->getContainer()?->serviceInstanceOthers);
        return ContainerController::getIncusClient($serviceInstanceOthers);
    }
}