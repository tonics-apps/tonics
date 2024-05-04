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