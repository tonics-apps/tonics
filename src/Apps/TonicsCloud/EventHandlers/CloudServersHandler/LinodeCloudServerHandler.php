<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers\CloudServersHandler;

use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterfaceAbstract;
use App\Apps\TonicsCloud\Library\Linode\LinodePricingServices;
use App\Apps\TonicsCloud\Library\Incus\IncusHelper;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;
use Generator;
use Linode\Exception\LinodeException;
use Linode\LinodeClient;
use Linode\LinodeInstances\Linode;
use Throwable;

require dirname(__FILE__, 3) . '/Library/Linode/Webinarium/vendor/autoload.php';

class LinodeCloudServerHandler extends CloudServerInterfaceAbstract
{
    const API_INSTANCES = '/linode/instances';
    const API_STACK_SCRIPTS = '/linode/stackscripts';

    public function displayName(): string
    {
        return LinodePricingServices::DisplayName;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return LinodePricingServices::PermName;
    }

    /**
     * @param array $data
     * @return void
     * @throws LinodeException
     * @throws Exception
     */
    public function createInstance(array $data): void
    {
        $client = $this->getLinodeClient();
        $cloudRegion = $data['cloud_region'] ?? '';
        $instanceName = $data['service_instance_name'] ?? '';
        $servicePlan = $data['service_plan'] ?? '';
        $serviceInstanceID = $data['service_instance_id'] ?? '';

        $service = InstanceController::getServicePlan($servicePlan);
        if (empty($serviceInstanceID) && $this->regionExist($cloudRegion) === false || !isset($service->service_name)) {
            throw new Exception("One or More Field Required To Create Instance is Missing");
        }

        $enableBackup = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodeBackup);

        $repository = $client->linodes;
        $parameters = [
            Linode::FIELD_TYPE => $service->service_name,
            Linode::FIELD_REGION => $cloudRegion,
            Linode::FIELD_BACKUPS_ENABLED => $enableBackup === '1',
            Linode::FIELD_ROOT_PASS => helper()->randString(),
        ];

        $deploymentOption = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodeDeploymentOption);
        $certs = IncusHelper::generateCertificateEncrypted();
        if ($deploymentOption === 'StackScript'){

            $stackScriptMode = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodeStackScriptMode);

            $parameters[ Linode::FIELD_IMAGE] = 'linode/debian12';
            if ($stackScriptMode === 'Production'){
                $parameters[ Linode::FIELD_STACKSCRIPT_ID] = 1196483;
                $parameters[Linode::FIELD_STACKSCRIPT_DATA] = [
                    'CERT' => $certs['cert'] // the client cert to be added to the server on deployment
                ];
            } else {
                $parameters[ Linode::FIELD_STACKSCRIPT_ID] = 1196477;
                $parameters[Linode::FIELD_STACKSCRIPT_DATA] = [
                    'USERNAME' => 'tonics-cloud',
                    'PASSWORD' => 'tonics-cloud',
                    'SSHKEY' => TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodeStackScriptSSHPublicKeyForDevMode),
                    'CERT' => $certs['cert'] // the client cert to be added to the server on deployment
                ];
            }

        } else {

            $parameters[ Linode::FIELD_IMAGE] = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodeImage);

        }

        $linode = $repository->createLinodeInstance($parameters);
        if (!empty($linode->toArray())) {
            db(onGetDB: function (TonicsQuery $db) use ($certs, $serviceInstanceID, $instanceName, $linode, $service) {
                $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
                $db->FastUpdate($serviceInstanceTable, [
                    'provider_instance_id' => $linode->id,
                    'service_instance_name' => $instanceName,
                    'others' => json_encode(
                        [
                            'serverHandlerName' => $this->name(),
                            'instance' => $linode->toArray(),
                            'ip' => [
                                'ipv4' => $linode->ipv4,
                                'ipv6' => $linode->ipv6,
                            ],
                            'security' => ['cert' => $certs, 'added' => false]
                        ])
                ], db()->Q()->WhereEquals('service_instance_id', $serviceInstanceID));
            });
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws Exception|Throwable
     */
    public function resizeInstance(array $data): void
    {
        $instanceName = $data['service_instance_name'] ?? '';
        $servicePlan = $data['service_plan'] ?? '';
        $providerInstanceID = $data['provider_instance_id'] ?? '';
        $customerID = $data['customer_id'] ?? '';

        $service = InstanceController::getServicePlan($servicePlan);
        if (!isset($service->service_name) && empty($customerID)) {
            throw new Exception("One or More Field Required To Update or Resize Instance is Missing");
        }

        $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
        db(onGetDB: function (TonicsQuery $db) use ($instanceName, $customerID, $serviceInstanceTable, $providerInstanceID, $service) {

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

                # Create a new one with the same property somewhat
                $db->Q()->Insert($serviceInstanceTable, [
                        'provider_instance_id' => $providerInstanceID, 'service_instance_name' => $instanceName,
                        'service_instance_status' => 'Resizing',
                        'fk_service_id' => $service->service_id, 'fk_provider_id' => $service->service_provider_id,
                        'fk_customer_id' => $customerID,
                        'others' => $serviceInstance->others,
                    ]
                );
                $client = $this->getLinodeClient();
                $client->linodes->resizeLinodeInstance($providerInstanceID, ['type' =>  $service->service_name]);
                $db->commit();
            } catch (Exception $exception) {
                $db->rollBack();
                InstanceController::updateContainerStatus("Failed To Resize, Reboot", $serviceInstance?->service_instance_id);
            }

        });


    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws Throwable
     */
    public function isStatus(array $data, string $statusString): bool
    {
        $status = '';
        if ($statusString === CloudServerInterface::STATUS_STOPPED) {
            $status = 'offline';
        }

        if ($statusString === CloudServerInterface::STATUS_RUNNING) {
            $status = 'running';
        }

        $providerInstanceID = self::ProviderInstanceID($data);

        if ($providerInstanceID) {
            $client = $this->getLinodeClient();
            $linodeData = $client->linodes->find($providerInstanceID)->toArray();
            if (isset($linodeData['status'])) {
                return $linodeData['status'] === $status;
            }
        }

        return false;
    }

    /**
     * @param array $data
     * @return void
     * @throws LinodeException
     * @throws Exception|Throwable
     */
    public function destroyInstance(array $data): void
    {
        $client = $this->getLinodeClient();
        $client->linodes->deleteLinodeInstance(self::ProviderInstanceID($data));
    }

    /**
     * @param array $data
     * @throws LinodeException
     * @throws Exception
     */
    public function changeInstanceStatus(array $data): void
    {
        $client = $this->getLinodeClient();
        $status = $data['service_instance_status'] ?? '';
        $statusAction = $data['service_instance_status_action'] ?? '';
        $instanceID = $data['provider_instance_id'] ?? '';

        if (empty($instanceID)) {
            return;
        }

        if (($status === 'Running' && $statusAction === 'Start') || ($status === 'Rebooting' && $statusAction === 'Reboot')) {
            return;
        }

        if ($statusAction === 'Reboot') {
            $client->linodes->rebootLinodeInstance($instanceID);
        }

        if ($statusAction === 'ShutDown') {
            $client->linodes->shutdownLinodeInstance($instanceID);
        }

        if ($statusAction === 'Start') {
            $client->linodes->bootLinodeInstance($instanceID);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws Throwable
     */
    public function instance(array $data): array
    {
        $providerInstanceID = self::ProviderInstanceID($data);
        $client = $this->getLinodeClient();
        return $client->linodes->find($providerInstanceID)->toArray();
    }


    /**
     * @throws Throwable
     */
    public function info(array $data): array
    {
        $instance = self::GetServiceInstances($data);
        if (isset($instance->others) && helper()->isJSON($instance->others)){
            $instance->others = json_decode($instance->others);
            return [
                'ipv4'   => $instance->others->ip->ipv4[array_key_first($instance->others->instance->ipv4)],
                'ipv6'   => $instance->others->ip->ipv6,
                'region' =>  $instance->others->instance->region
            ];
        }
        return [];
    }

    /**
     * @param array $data
     * @return mixed
     * @throws Exception|Throwable
     */
    public function instanceStatus(array $data): mixed
    {
        $instanceInfo = $this->instance($data);
        if (isset($instanceInfo['status'])) {
            return $instanceInfo['status'];
        }

        return null;
    }

    /**
     * @param array $data
     * @return Generator
     * @throws LinodeException
     * @throws Exception
     */
    public function instances(array $data): Generator
    {
        $json = null;
        $page = $data['page'] ?? 1;
        $maxPages = $data['maxPages'] ?? null;
        $nextPageHandler = $data['nextPageHandler'] ?? null;
        $uri = $data['uri'] ?? self::API_INSTANCES;

        $client = $this->getLinodeClient();
        while (($json === null || $page <= $json['pages']) && ($maxPages === null || $page <= $maxPages)) {
            $response = null;
            try {
                $response = $client->get($uri, ['page' => $page, 'page_size' => 200]);
            } catch (LinodeException $exception) {
                throw new Exception("An Error Occurred Reaching API EndPoint");
            }

            $X_RateLimit_Remaining = $response->getHeader('X-RateLimit-Remaining');
            $remaining = 0;
            if (is_array($X_RateLimit_Remaining) && isset($X_RateLimit_Remaining[0])) {
                $remaining = (int)$X_RateLimit_Remaining[0];
            }

            if ($response->getStatusCode() === 200 && $remaining > 0) {
                $contents = $response->getBody()->getContents();
                $json = json_decode($contents, true);

                // Process the instances or yield them as a generator
                $instances = $json['data'];
                foreach ($instances as $instance) {
                    yield $instance;
                }

                // Increment the page number to fetch the next page
                $page++;

                // Call the next page handler if there is a next page
                if ($page <= $json['pages'] && is_callable($nextPageHandler)) {
                    $nextPageHandler($page, $json['pages']);
                }
            } else {
                // Handle error or rate limit exceeded
                $contents = $response->getBody()->getContents();
                $json = json_decode($contents, true);
                $pileUp = '';
                if (isset($json['errors']) && is_array($json['errors'])) {
                    foreach ($json['errors'] as $error) {
                        if (isset($error['reason'])) {
                            $pileUp .= " / " . $error['reason'];
                        }
                    }
                }
                throw new Exception(' Status Code: ' . $response->getStatusCode() . " Reason: $pileUp");
            }
        }
    }


    /**
     * @return LinodeClient
     * @throws \Exception
     */
    private function getLinodeClient(): LinodeClient
    {
        return new LinodeClient(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodeAPIToken));
    }

    /**
     * @return array[]
     * @throws Exception
     */
    public function regions(): array
    {
        $regions = TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::LinodeRegion);
        if (helper()->isJSON($regions)) {
            $regions = json_decode($regions, true);
        } else {
            $regions = [
                ['label' => 'Dallas, TX', 'id' => 'us-central'],
                ['label' => 'Mumbai, IN', 'id' => 'ap-west'],
                ['label' => 'Toronto, CA', 'id' => 'ca-central'],
                ['label' => 'Sydney, AU', 'id' => 'ap-southeast'],
                ['label' => 'Fremont, CA', 'id' => 'us-west'],
                ['label' => 'Atlanta, GA', 'id' => 'us-southeast'],
                ['label' => 'Newark, NJ', 'id' => 'us-east'],
                ['label' => 'London, UK', 'id' => 'eu-west'],
                ['label' => 'Singapore, SG', 'id' => 'ap-south'],
                ['label' => 'Frankfurt, DE', 'id' => 'eu-central'],
                ['label' => 'Tokyo, JP', 'id' => 'ap-northeast'],
            ];
        }

        return $regions;
    }

    /**
     * @throws Exception
     */
    public function prices(): array
    {
        return LinodePricingServices::priceList();
    }
}