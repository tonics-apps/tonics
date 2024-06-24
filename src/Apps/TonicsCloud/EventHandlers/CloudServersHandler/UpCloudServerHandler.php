<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\EventHandlers\CloudServersHandler;

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterfaceAbstract;
use App\Apps\TonicsCloud\Interfaces\DefaultJobQueuePaths;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueInstanceHasStopped;
use App\Apps\TonicsCloud\Jobs\Instance\CloudJobQueueStopInstance;
use App\Apps\TonicsCloud\Library\Incus\IncusHelper;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Upcloud\ApiClient\ApiException;
use Upcloud\ApiClient\Configuration;
use Upcloud\ApiClient\Model\CreateServerRequest;
use Upcloud\ApiClient\Model\CreateServerResponse;
use Upcloud\ApiClient\Model\IpAddress;
use Upcloud\ApiClient\Model\IpAddresses;
use Upcloud\ApiClient\Model\ModifyServerRequest;
use Upcloud\ApiClient\Model\NetworkInterface;
use Upcloud\ApiClient\Model\NetworkInterfaceList;
use Upcloud\ApiClient\Model\NetworkInterfaces;
use Upcloud\ApiClient\Model\RestartServer;
use Upcloud\ApiClient\Model\Server;
use Upcloud\ApiClient\Model\ServerLoginUser;
use Upcloud\ApiClient\Model\ServerSshKey;
use Upcloud\ApiClient\Model\ServerStorageDevices;
use Upcloud\ApiClient\Model\StopServer;
use Upcloud\ApiClient\Model\StorageDevice;
use Upcloud\ApiClient\Upcloud\ServerApi;

require dirname(__FILE__, 3) . '/Library/UpCloud/vendor/autoload.php';

class UpCloudServerHandler extends CloudServerInterfaceAbstract
{

    /**
     * @inheritDoc
     */
    public function displayName (): string
    {
        return 'UpCloud';
    }

    /**
     * @inheritDoc
     */
    public function name (): string
    {
        return 'UpCloud';
    }

    /**
     * @throws GuzzleException
     * @throws ApiException
     * @throws Exception
     */
    public function createInstance (array $data): void
    {
        $this->configureUpCloudClient();

        $cloudRegion = $data['cloud_region'] ?? '';
        $instanceName = $data['service_instance_name'] ?? '';
        $servicePlan = $data['service_plan'] ?? '';
        $serviceInstanceID = $data['service_instance_id'] ?? '';

        $devMode = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::UpCloudMode);
        $mode = $devMode === 'Production';

        $service = InstanceController::getServicePlan($servicePlan);
        if (empty($serviceInstanceID) && $this->regionExist($cloudRegion) === false || !isset($service->service_name)) {
            throw new Exception("One or More Field Required To Create Instance is Missing");
        }

        $servicePlanKey = str_replace('UpCloud-', '', $service->service_name);

        $serviceOthers = json_decode($service->others);
        $certs = IncusHelper::generateCertificateEncrypted();
        $sshKey = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::UpCloudSSHPublicKeyForDevMode);

        $loginUser = new ServerLoginUser();
        $loginUser->setUsername('root');

        if ($mode === false) {
            $serverSshKey = new ServerSshKey();
            $serverSshKey->setSshKey([$sshKey]);
            $loginUser->setSshKeys($serverSshKey);
        }

        $loginUser->setCreatePassword('no');

        $serverUniqueName = 'tc-uc-' . helper()->randString(15);
        $server = new Server();
        $server->setTitle($serverUniqueName);
        $server->setZone($cloudRegion);
        $server->setPlan($servicePlanKey);
        $server->setHostname('tonics-cloud');
        $server->setPasswordDelivery('none');
        $server->setMetadata('yes');
        $server->setUserData($this->initializationScript($certs['cert'], $sshKey, $mode, self::IncusPort()));
        $server->setLoginUser($loginUser);
        $server->setFirewall('off');

        $networkInterfaceList = new NetworkInterfaceList();
        $networkInterfaces = new NetworkInterfaces();

        $networkInterfaceIPV4 = new NetworkInterface();
        $networkInterfaceIPV6 = new NetworkInterface();
        $ipAddresses = new IpAddresses();
        $ipAddresses->setIpAddress(
            [
                (new IpAddress())->setFamily('IPv4'),
            ],
        );

        $ipAddressesV6 = new IpAddresses();
        $ipAddressesV6->setIpAddress(
            [
                (new IpAddress())->setFamily('IPv6'),
            ],
        );

        $networkInterfaceIPV4->setType('public')
            ->setIpAddresses($ipAddresses);
        $networkInterfaceIPV6->setType('public')
            ->setIpAddresses($ipAddressesV6);

        $networkInterfaces->setInterface([$networkInterfaceIPV4, $networkInterfaceIPV6]);
        $networkInterfaceList->setInterfaces($networkInterfaces);

        $server->setNetworking($networkInterfaceList);

        $storage = new StorageDevice();
        $storage->setStorage('01000000-0000-4000-8000-000020070100'); // Debian 12 - Bookworm
        $storage->setSize($serviceOthers->disk);
        $storage->setAction('clone');
        $storage->setTitle('debian-bookworm-storage');

        $storageDevices = new ServerStorageDevices();
        $storageDevices->setStorageDevice([$storage]);

        $server->setStorageDevices($storageDevices);

        $serverRequest = new CreateServerRequest();
        $serverRequest->setServer($server);
        $serverApi = new ServerApi();

        $serverCreatedResult = $serverApi->createServer($serverRequest);

        db(onGetDB: function (TonicsQuery $db) use ($certs, $serviceInstanceID, $instanceName, $serverCreatedResult, $service) {
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $server = $serverCreatedResult->getServer();

            $IpInfo = [];
            foreach ($server->getIpAddresses()->getIpAddress() as $ipAddress) {
                $IpInfo[strtolower($ipAddress->getFamily())][] = $ipAddress->getAddress();
            }

            $db->FastUpdate($serviceInstanceTable, [
                'provider_instance_id'  => $server->getUuid(),
                'service_instance_name' => $instanceName,
                'others'                => json_encode(
                    [
                        'serverHandlerName' => $this->name(),
                        'instance'          => serialize($server),
                        'ip'                => $IpInfo,
                        'security'          => ['cert' => $certs, 'added' => false],
                    ]),
            ], db()->Q()->WhereEquals('service_instance_id', $serviceInstanceID));
        });
    }

    /**
     * @throws \Throwable
     * @throws GuzzleException
     * @throws ApiException
     */
    public function destroyInstance (array $data): void
    {
        $this->configureUpCloudClient();
        $serverApi = new ServerApi();
        $instanceID = self::ProviderInstanceID($data);
        if (empty($instanceID)) {
            return;
        }
        $serverApi->deleteServer($instanceID, true);
    }

    /**
     * @throws \Throwable
     */
    public function resizeInstance (array $data): void
    {
        $instanceName = $data['service_instance_name'] ?? '';
        $servicePlan = $data['service_plan'] ?? '';
        $providerInstanceID = $data['provider_instance_id'] ?? '';
        $customerID = $data['customer_id'] ?? '';

        $service = InstanceController::getServicePlan($servicePlan);
        if (!isset($service->service_name) && empty($customerID)) {
            throw new Exception("One or More Field Required To Update or Resize Instance is Missing");
        }

        $this->configureUpCloudClient();

        $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
        db(/**
         * @throws \Throwable
         */ onGetDB: function (TonicsQuery $db) use ($instanceName, $customerID, $serviceInstanceTable, $providerInstanceID, $service) {

            $settings = [
                'instance_id' => $providerInstanceID,
            ];
            $serviceInstance = InstanceController::GetServiceInstances($settings);

            $db->beginTransaction();
            try {
                # End The Previous Instance
                $endTime = date('Y-m-d H:i:s');
                $db->FastUpdate($serviceInstanceTable, ['end_time' => $endTime, 'service_instance_status' => 'Resized'],
                    db()->Q()->WhereEquals('provider_instance_id', $providerInstanceID)->WhereNull('end_time'));

                $serviceOthers = json_decode($service->others);
                $serverApi = new ServerApi();
                $server = new ModifyServerRequest();
                $servicePlanKey = str_replace('UpCloud-', '', $service->service_name);
                $server->getServer()
                    ->setPlan($servicePlanKey)
                    ->setCoreNumber($serviceOthers->core)
                    ->setMemoryAmount($serviceOthers->memory);

                $result = $serverApi->modifyServer($providerInstanceID, $server);
                $serviceInstanceOthers = json_decode($serviceInstance->others);
                $serviceInstanceOthers->instance = serialize($result->getServer());

                # Create a new one with the same property somewhat
                $db->Q()->Insert($serviceInstanceTable, [
                    'provider_instance_id'    => $providerInstanceID, 'service_instance_name' => $instanceName,
                    'service_instance_status' => 'Resizing',
                    'fk_service_id'           => $service->service_id, 'fk_provider_id' => $service->service_provider_id,
                    'fk_customer_id'          => $customerID,
                    'others'                  => json_encode($serviceInstanceOthers),
                ],
                );

                $db->commit();
            } catch (Exception|GuzzleException $exception) {
                $db->rollBack();
                InstanceController::updateContainerStatus("Failed To Resize, Reboot", $serviceInstance?->service_instance_id);
            }

        });
    }

    /**
     * @throws ApiException
     * @throws GuzzleException
     * @throws Exception
     */
    public function changeInstanceStatus (array $data): void
    {
        $this->configureUpCloudClient();
        $status = $data['service_instance_status'] ?? '';
        $statusAction = $data['service_instance_status_action'] ?? '';
        $instanceID = $data['provider_instance_id'] ?? '';

        if (empty($instanceID)) {
            return;
        }

        $serverApi = new ServerApi();

        if (($status === 'Running' && $statusAction === 'Start') || ($status === 'Rebooting' && $statusAction === 'Reboot')) {
            return;
        }

        if ($statusAction === 'Reboot') {
            $serverApi->restartServer($instanceID, new RestartServer());
        }

        if ($statusAction === 'ShutDown') {
            $serverApi->stopServer($instanceID, new StopServer());
        }

        if ($statusAction === 'Start') {
            $serverApi->startServer($instanceID);
        }
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function isStatus (array $data, string $statusString): bool
    {
        $this->configureUpCloudClient();
        $status = '';
        if ($statusString === CloudServerInterface::STATUS_STOPPED) {
            $status = 'stopped';
        }

        if ($statusString === CloudServerInterface::STATUS_RUNNING) {
            $status = 'started';
        }

        $providerInstanceID = self::ProviderInstanceID($data);

        if ($providerInstanceID) {
            $serverDetails = $this->instance($data);
            return $serverDetails->getServer()->getState() === $status;
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function instanceStatus (array $data): mixed
    {
        return $this->instance($data)->getServer()->getState();
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function instance (array $data): CreateServerResponse|null
    {
        $this->configureUpCloudClient();
        $serverApi = new ServerApi();
        $instanceID = self::ProviderInstanceID($data);
        if (empty($instanceID)) {
            return null;
        }
        return $serverApi->serverDetails(self::ProviderInstanceID($data));
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function info (array $data): array
    {
        $instance = self::GetServiceInstances($data);
        if (isset($instance->others) && helper()->isJSON($instance->others)) {
            $instance->others = json_decode($instance->others);
            /** @var Server $server */
            $server = unserialize($instance->others->instance);
            return [
                'ipv4'   => isset($instance->others->ip->ipv4) ? $instance->others->ip->ipv4[array_key_first($instance->others->ip->ipv4)] : null,
                'ipv6'   => (isset($instance->others->ip->ipv6)) ? $instance->others->ip->ipv6[array_key_first($instance->others->ip->ipv6)] : null,
                'region' => $server->getZone(),
            ];
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function instances (array $data): \Generator
    {
        // TODO: Implement instances() method.
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function regions (): array
    {
        $regions = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::UpCloudRegion);
        if (helper()->isJSON($regions)) {
            $regions = json_decode($regions, true);
        } else {
            $regions = [];
        }

        return $regions;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function prices (): array
    {
        $prices = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::UpCloudPriceList);
        if (helper()->isJSON($prices)) {
            $prices = json_decode($prices, true);
        } else {
            $prices = [];
        }

        return $prices;
    }

    public static function TerminateInstanceJobQueuePaths (): array
    {
        return [
            [
                'job'      => new CloudJobQueueStopInstance(),
                'children' => [
                    [
                        'job'      => new CloudJobQueueInstanceHasStopped(),
                        'children' => DefaultJobQueuePaths::TerminateInstanceJobQueuePaths(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    private function configureUpCloudClient (): void
    {
        // Configure HTTP basic authorization: baseAuth
        Configuration::getDefaultConfiguration()->setUsername(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::UpCloudUserName));
        Configuration::getDefaultConfiguration()->setPassword(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::UpCloudPassword));
    }
}