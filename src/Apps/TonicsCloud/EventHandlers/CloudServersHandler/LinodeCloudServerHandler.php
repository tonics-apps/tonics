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
use App\Apps\TonicsCloud\Events\OnAddCloudServerEvent;
use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Library\Linode\LinodePricingServices;
use App\Apps\TonicsCloud\Library\LXD\LXDHelper;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\Authentication\Session;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;
use Generator;
use Linode\Entity\Linode;
use Linode\Exception\LinodeException;
use Linode\LinodeClient;

require dirname(__FILE__, 3) . '/Library/Linode/Webinarium/vendor/autoload.php';

class LinodeCloudServerHandler implements HandlerInterface, CloudServerInterface
{
    const API_INSTANCES = '/linode/instances';
    const API_STACK_SCRIPTS = '/linode/stackscripts';

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnAddCloudServerEvent */
        $event->addCloudServerHandler($this);
    }

    public function name(): string
    {
        return LinodePricingServices::PermName;
    }

    /**
     * @throws \Exception
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

        $repository = $client->linodes;
        $parameters = [
            Linode::FIELD_TYPE => $service->service_name,
            Linode::FIELD_REGION => $cloudRegion,
            Linode::FIELD_BACKUPS_ENABLED => true,
            Linode::FIELD_IMAGE => 'private/20349386',
            Linode::FIELD_ROOT_PASS => helper()->randString(),
        ];

        $linode = $repository->create($parameters);
        if (!empty($linode->toArray())) {
            db(onGetDB: function (TonicsQuery $db) use ($serviceInstanceID, $instanceName, $linode, $service) {
                $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
                $db->FastUpdate($serviceInstanceTable, [
                    'provider_instance_id' => $linode->id,
                    'service_instance_name' => $instanceName,
                    'others' => json_encode(
                        [
                            'instance' => $linode->toArray(),
                            'security' => ['cert' => LXDHelper::generateCertificateEncrypted(), 'added' => false]
                        ])
                ], db()->Q()->WhereEquals('service_instance_id', $serviceInstanceID));
            });
        }
    }

    /**
     * @throws LinodeException
     * @throws Exception
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
        $client = $this->getLinodeClient();

        # End The Previous Instance
        db(onGetDB: function (TonicsQuery $db) use ($serviceInstanceTable, $providerInstanceID, $service) {
            $endTime = date('Y-m-d H:i:s');
            $db->FastUpdate($serviceInstanceTable, ['end_time' => $endTime, 'service_instance_status' => 'Resized'],
                db()->Q()->WhereEquals('provider_instance_id', $providerInstanceID)->WhereNull('end_time'));
        });

        # Create a new one with the same property somewhat
        db(onGetDB: function (TonicsQuery $db) use ($providerInstanceID, $customerID, $instanceName, $serviceInstanceTable, $service) {
            $db->Insert($serviceInstanceTable, [
                    'provider_instance_id' => $providerInstanceID, 'service_instance_name' => $instanceName,
                    'service_instance_status' => 'Resizing',
                    'fk_service_id' => $service->service_id, 'fk_provider_id' => $service->service_provider_id,
                    'fk_customer_id' => $customerID,
                ]
            );
        });

        $client->linodes->resize($providerInstanceID, $service->service_name);
    }

    /**
     * @throws Exception
     */
    public function destroyInstance(array $data): void
    {
        $items = $data;
        $client = $this->getLinodeClient();
        $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
        foreach ($items as $item) {
            $item = (array)$item;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::';
            $instanceID = $item[$serviceInstancePrefix . 'provider_instance_id'] ?? '';
            if (empty($instanceID)) {
                throw new Exception("One or More Field Required To Destroy Instance is Missing");
            }

            db(onGetDB: function (TonicsQuery $db) use ($serviceInstanceTable, $instanceID) {
                $endTime = date('Y-m-d H:i:s');
                $db->FastUpdate($serviceInstanceTable, ['end_time' => $endTime, 'service_instance_status' => 'Destroyed'], db()->Q()->WhereEquals('provider_instance_id', $instanceID)->WhereNull('end_time'));
            });
            $client->linodes->delete($instanceID);
        }
    }

    /**
     * @param array $data
     * @throws LinodeException
     * @throws Exception
     */
    public function changeInstanceStatus(array $data): void
    {
        $updates = $data;
        $client = $this->getLinodeClient();
        foreach ($updates as $update) {
            $update = (array)$update;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::';
            $status = $update[$serviceInstancePrefix . 'service_instance_status'] ?? '';
            $statusAction = $update[$serviceInstancePrefix . 'service_instance_status_action'] ?? '';
            $instanceID = $update[$serviceInstancePrefix . 'provider_instance_id'] ?? '';

            if (empty($instanceID)) {
                continue;
            }

            if (($status === 'Running' && $statusAction === 'Start') || ($status === 'Rebooting' && $statusAction === 'Reboot')) {
                continue;
            }

            if ($statusAction === 'Reboot') {
                $client->linodes->reboot($instanceID);
            }

            if ($statusAction === 'ShutDown') {
                $client->linodes->shutdown($instanceID);
            }

            if ($statusAction === 'Start') {
                $client->linodes->boot($instanceID);
            }
        }
    }

    /**
     * @param array $data
     * @return Generator
     * @throws LinodeException
     * @throws Exception
     */
    public function getInstances(array $data): Generator
    {
        $json = null;
        $page = $data['page'] ?? 1;
        $errorHandler = $data['errorHandler'] ?? null;
        $maxPages = $data['maxPages'] ?? null;
        $nextPageHandler = $data['nextPageHandler'] ?? null;
        $uri = $data['uri'] ?? self::API_INSTANCES;

        $client = $this->getLinodeClient();
        while (($json === null || $page <= $json['pages']) && ($maxPages === null || $page <= $maxPages)) {
            $response = null;
            try {
                $response = $client->api($client::REQUEST_GET, $uri, ['page' => $page, 'page_size' => 200]);
            } catch (LinodeException $exception) {
                if (is_callable($errorHandler)) {
                    $errorHandler($response, $exception->getMessage());
                }
                break;
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
                if (is_callable($errorHandler)) {
                    $errorHandler($response, '');
                }
                break;
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
     * @param string $name
     * @return bool
     */
    private function regionExist(string $name): bool
    {
        foreach ($this->regions() as $region) {
            if ($region['id'] === $name) {
                return true;
            }
        }

        return false;
    }

    public function regions(): array
    {
        return [
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
}