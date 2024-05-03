<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Jobs\Instance\Traits;

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterfaceAbstract;
use App\Apps\TonicsCloud\TonicsCloudActivator;

trait TonicsJobQueueInstanceTrait
{
    /**
     * @param string $statusMsg
     * @return void
     * @throws \Exception
     */
    public function updateContainerStatus(string $statusMsg): void
    {
        InstanceController::updateContainerStatus($statusMsg, $this->getServiceInstanceID());
    }

    /**
     * @return mixed|string
     * @throws \Exception
     * @throws \Throwable
     */
    public function getServiceInstanceID(): mixed
    {
        $serviceInstanceID = $this->getDataAsArray()['service_instance_id'] ?? '';

        if (!empty($serviceInstanceID)){
            return $serviceInstanceID;
        }

        $providerInstanceID = $this->getDataAsArray()['provider_instance_id'] ?? '';
        if (!empty($providerInstanceID)){
            $settings = [
                'instance_id' => $providerInstanceID,
            ];
            $serviceInstanceID = InstanceController::GetServiceInstances($settings)?->service_instance_id;
        }
        return $serviceInstanceID;
    }

    /**
     * @return mixed|null
     * @throws \Throwable
     */
    public function getServiceInstance(): mixed
    {
        $instanceID = $this->getDataAsArray()['service_instance_id'] ?? '';
        $providerInstanceID = $this->getDataAsArray()['provider_instance_id'] ?? '';
        $serviceInstance = null;

        $column = 'service_instance_id';
        if (empty($instanceID)) {
            $instanceID = $providerInstanceID;
            $column = 'provider_instance_id';
        }

        if (!empty($instanceID)){

            $settings = [
                'instance_id' => $instanceID,
                'column' => $column,
            ];
            $serviceInstance = InstanceController::GetServiceInstances($settings);

        }

        return $serviceInstance;
    }

    /**
     * @param $serviceInstance
     * @return mixed|object
     */
    public function getServiceInstanceOthers($serviceInstance): mixed
    {
        if (isset($serviceInstance->others)) {
            return json_decode($serviceInstance->others);
        }

        return (object)[];
    }

    /**
     * @throws \Throwable
     */
    public function getHandler(): CloudServerInterfaceAbstract
    {
        $handlerName = $this->getDataAsArray()['handlerName'] ?? '';
        if (empty($handlerName)) {
            $handlerName = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType);
        }

        return TonicsCloudActivator::getCloudServerHandler($handlerName);
    }
}