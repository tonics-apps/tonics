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

namespace App\Apps\TonicsCloud\Controllers;

use App\Apps\TonicsCloud\EventHandlers\Messages\TonicsCloudInstanceMessage;
use App\Apps\TonicsCloud\Interfaces\DefaultJobQueuePaths;
use App\Apps\TonicsCloud\Interfaces\QueuePathHelper;
use App\Apps\TonicsCloud\Services\InstanceService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Throwable;

class InstanceController
{
    use Validator;

    const CREATE_METHOD = 'CREATE';
    const EDIT_METHOD   = 'EDIT';
    private static string $currentControllerMethod = '';

    /**
     * @param FieldData $fieldData
     * @param AbstractDataLayer $abstractDataLayer
     * @param InstanceService $instanceService
     */
    public function __construct (private FieldData $fieldData, private readonly AbstractDataLayer $abstractDataLayer, private readonly InstanceService $instanceService) {}

    /**
     * @return void
     * @throws \Exception
     * @throws Throwable
     */
    public function index (): void
    {
        $data = null;
        db(onGetDB: function (TonicsQuery $db) use (&$data) {
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $serviceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);

            $data = $db->Select("service_instance_status, service_instance_id, provider_instance_id, 
            service_instance_name, {$this->instanceService::EditLinkColumn()}, service_description")
                ->From("$serviceInstanceTable")
                ->Join("$serviceTable", "$serviceInstanceTable.fk_service_id", "$serviceTable.service_id")
                ->WhereEquals('fk_customer_id', \session()::getUserID())->WhereNull('end_time')
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('service_instance_name', url()->getParam('query'));
                })
                ->OrderByDesc(table()->pickTable($serviceInstanceTable, ['created_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Apps::TonicsCloud/Views/Instance/index', [
            'DataTable' => [
                'headers'       => $this->instanceService::DataTableHeaders(),
                'paginateData'  => $data ?? [],
                'dataTableType' => 'TONICS_CLOUD',
                'messageURL'    => route('messageEvent', [TonicsCloudInstanceMessage::MessageTypeKey(\session()::getUserID())]),
            ],
            'SiteURL'   => AppConfig::getAppUrl(),
        ]);

    }

    /**
     * @throws \Exception
     * @throws Throwable
     */
    public function dataTable (): void
    {
        $entityBag = null;
        if ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deletion Enqueued", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Update Enqueued", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500, 'An Error Occurred Updating Records');
            }
        }
    }

    /**
     * @throws \Exception
     * @throws Throwable
     */
    public function create (): void
    {
        self::setCurrentControllerMethod(self::CREATE_METHOD);
        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCloud/Views/Instance/create', [
            'SiteURL'    => AppConfig::getAppUrl(),
            'TimeZone'   => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-instance-page'], $oldFormInput)->getHTMLFrag(),
        ]);
    }


    /**
     * @param \stdClass $others
     * @param $serviceInstanceID
     * @param string $col
     * -- can be provider_instance_id or service_instance_id or whatever $serviceInstanceID matches against
     *
     * @return void
     * @throws \Exception
     */
    public static function updateInstanceServiceOthers (\stdClass $others, $serviceInstanceID, string $col = 'provider_instance_id'): void
    {
        db(onGetDB: function (TonicsQuery $db) use ($col, $serviceInstanceID, $others) {
            $col = table()->pick([TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES) => [$col]]);
            $db->Q()->Update(TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES))
                ->Set('others', json_encode($others))
                ->WhereNull("end_time")
                ->WhereEquals($col, $serviceInstanceID)
                ->Exec();
        });
    }

    /**
     * @param $providerInstanceID
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Exception|Throwable
     */
    public function update ($providerInstanceID)
    {
        $validator = $this->getValidator();
        $validation = $validator->make(input()->fromPost()->all(), $this->getInstanceUpdateRule());
        if ($validation->fails()) {
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.instances.edit', [$providerInstanceID]));
        }

        # If this returns false, then user want to change the name
        if (input()->fromPost()->hasValue('service_plan')) {
            if (\session()::getUserID() !== null) {
                $settings = [
                    'instance_id' => $providerInstanceID,
                    'user_id'     => \session()::getUserID(),
                ];
                $serviceInstance = self::GetServiceInstances($settings);
                # Yh, we should never pass service_instance_id here because it might have been resized, so, provider_instance_id is enough
                $jobData = [
                    'service_plan'          => input()->fromPost()->retrieve('service_plan'),
                    'service_instance_name' => $serviceInstance->service_instance_name,
                    'provider_instance_id'  => $providerInstanceID,
                    'customer_id'           => \session()::getUserID(),
                ];

                $jobs = QueuePathHelper::InstancePath($serviceInstance, DefaultJobQueuePaths::PATH_INSTANCE_RESIZE, function ($handlerName) use (&$jobData) {
                    $jobData['handlerName'] = $handlerName;
                });

                self::updateContainerStatus('Resizing', $serviceInstance->service_instance_id);
                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);

                session()->flash(['Instance Update Enqueued'], [], Session::SessionCategories_FlashMessageSuccess);
                redirect(route('tonicsCloud.instances.index'));
            }
        }

        db(onGetDB: function (TonicsQuery $db) use ($providerInstanceID) {
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $db->FastUpdate($serviceInstanceTable, ['service_instance_name' => input()->fromPost()->retrieve('service_instance_name')],
                db()->Q()->WhereEquals('provider_instance_id', $providerInstanceID)->WhereNull('end_time'));
        });

        session()->flash(['Update Successful'], [], Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsCloud.instances.index'));
    }

    /**
     * @throws \Exception
     */
    public function getInstanceUpdateRule (): array
    {
        return [
            'service_instance_name' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 255]],
        ];
    }

    /**
     * Settings can contain
     * - `instance_id` (optional)- if this is empty, ensure `user_id` is not empty, meaning, it would retrieve instances for the user_id
     * - `column` (optional) - column to check against, defaults to `provider_instance_id`
     * - `user_id` (optional)
     * - `fetch_all` (optional) - boolean, defaults to false
     *
     * @param array $settings
     *
     * @return mixed|null
     * @throws Throwable
     */
    public static function GetServiceInstances (array $settings): mixed
    {
        return InstanceService::GetServiceInstances($settings);
    }

    /**
     * @param string $statusMsg
     * @param $serviceInstanceID
     *
     * @return void
     * @throws \Exception
     */
    public static function updateContainerStatus (string $statusMsg, $serviceInstanceID): void
    {
        $statusMsg = ucwords(str_replace('_', ' ', $statusMsg));
        db(onGetDB: function (TonicsQuery $db) use ($serviceInstanceID, $statusMsg) {
            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $db->Q()->Update($table)
                ->Set('service_instance_status', $statusMsg)
                ->WhereNull("end_time")
                ->WhereEquals('service_instance_id', $serviceInstanceID)
                ->Exec();
        });
    }

    /**
     * @return string
     */
    public static function getCurrentControllerMethod (): string
    {
        return self::$currentControllerMethod;
    }

    /**
     * @param string $currentControllerMethod
     */
    public static function setCurrentControllerMethod (string $currentControllerMethod): void
    {
        self::$currentControllerMethod = $currentControllerMethod;
    }

    /**
     * @return AbstractDataLayer
     */
    public function getAbstractDataLayer (): AbstractDataLayer
    {
        return $this->abstractDataLayer;
    }

    /**
     * @param $entityBag
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function deleteMultiple ($entityBag): bool
    {
        $deleteItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
        foreach ($deleteItems as $delete) {
            $delete = (array)$delete;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::';
            $providerInstanceID = $delete[$serviceInstancePrefix . 'provider_instance_id'] ?? '';
            $settings = [
                'instance_id' => $providerInstanceID,
                'user_id'     => \session()::getUserID(),
            ];
            $serviceInstance = self::GetServiceInstances($settings);
            self::TerminateInstances([$serviceInstance]);
        }
        return true;
    }

    /**
     * @param array $instances
     *
     * @return void
     * @throws \Exception
     * @throws Throwable
     */
    public static function TerminateInstances (array $instances): void
    {
        foreach ($instances as $instance) {
            if (isset($instance->service_instance_id)) {
                $jobData = [
                    'service_instance_id'  => $instance->service_instance_id,
                    'provider_instance_id' => $instance->provider_instance_id,
                ];

                $jobs = QueuePathHelper::InstancePath($instance, DefaultJobQueuePaths::PATH_INSTANCE_TERMINATE, function ($handlerName) use (&$jobData) {
                    $jobData['handlerName'] = $handlerName;
                });
                self::updateContainerStatus('Destroying', $instance->service_instance_id);
                TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
            }
        }
    }

    /**
     * @param $entityBag
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function updateMultiple ($entityBag): bool
    {
        $updateItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
        foreach ($updateItems as $update) {
            $update = (array)$update;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::';
            $providerInstanceID = $update[$serviceInstancePrefix . 'provider_instance_id'] ?? '';
            $settings = [
                'instance_id' => $providerInstanceID,
                'user_id'     => \session()::getUserID(),
            ];
            $serviceInstance = self::GetServiceInstances($settings);
            $status = $update[$serviceInstancePrefix . 'service_instance_status_action'] ?? '';

            if ($serviceInstance) {
                if ($serviceInstance->service_instance_status === 'Running' && $status === 'Start') {
                    continue;
                }

                if ($serviceInstance->service_instance_status === 'Offline' && $status === 'ShutDown') {
                    continue;
                }

                $jobData = [
                    'service_instance_id'            => $serviceInstance->service_instance_id,
                    'provider_instance_id'           => $providerInstanceID,
                    'service_instance_status_action' => $status,
                    'service_instance_status'        => $serviceInstance->service_instance_status,
                ];

                if ($status === 'Terminate') {
                    self::TerminateInstances([$serviceInstance]);
                }

                if ($status === 'Start') {
                    $jobs = QueuePathHelper::InstancePath($serviceInstance, DefaultJobQueuePaths::PATH_INSTANCE_START, function ($handlerName) use (&$jobData) {
                        $jobData['handlerName'] = $handlerName;
                    });
                    self::updateContainerStatus('Starting', $serviceInstance->service_instance_id);
                    TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                }

                if ($status === 'ShutDown') {
                    $jobs = QueuePathHelper::InstancePath($serviceInstance, DefaultJobQueuePaths::PATH_INSTANCE_SHUT_DOWN, function ($handlerName) use (&$jobData) {
                        $jobData['handlerName'] = $handlerName;
                    });
                    self::updateContainerStatus('Shutting Down', $serviceInstance->service_instance_id);
                    TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                }

                if ($status === 'Reboot') {
                    $jobs = QueuePathHelper::InstancePath($serviceInstance, DefaultJobQueuePaths::PATH_INSTANCE_REBOOT, function ($handlerName) use (&$jobData) {
                        $jobData['handlerName'] = $handlerName;
                    });
                    self::updateContainerStatus('Rebooting', $serviceInstance->service_instance_id);
                    TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                }

            }

        }

        return true;
    }

    /**
     * @return FieldData
     */
    public function getFieldData (): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData (FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     * @throws Throwable
     */
    public function store ()
    {
        $validator = $this->getValidator();
        $validator->changeErrorMessage(['service_plan:required' => 'Please Choose a Plan']);

        $validation = $validator->make(input()->fromPost()->all(), $this->getInstanceCreateRule());
        $service = InstanceController::getServicePlan(input()->fromPost()->retrieve('service_plan'));

        if ($validation->fails() || !isset($service->service_name)) {
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.instances.create'));
        }

        $insertedInstance = null;
        db(onGetDB: function (TonicsQuery $db) use ($service, &$insertedInstance) {
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            if (session()::getUserID() !== null) {
                $insertedInstance = $db->InsertReturning($serviceInstanceTable, [
                    'service_instance_name' => input()->fromPost()->retrieve('service_instance_name'),
                    'fk_customer_id'        => \session()::getUserID(), 'fk_service_id' => $service->service_id, 'fk_provider_id' => $service->service_provider_id,
                ], ['service_instance_id'], 'service_instance_id',
                );
            }
        });

        if ($insertedInstance === null) {
            session()->flash(['An Error Occurred Creating an Instance'], input()->fromPost()->all());
            redirect(route('tonicsCloud.instances.create'));
        }

        $jobs = QueuePathHelper::InstancePath(null, DefaultJobQueuePaths::PATH_INSTANCE_CREATE);

        $_POST['service_instance_id'] = $insertedInstance->service_instance_id;
        unset($_POST['_fieldDetails']); # Clean Rubbish

        TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $_POST);


        session()->flash(['Instance Creation Enqueued'], [], Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsCloud.instances.index'));
    }

    /**
     * @throws \Exception
     */
    public function getInstanceCreateRule (): array
    {
        return [
            'service_instance_name' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 255]],
            'cloud_region'          => ['required', 'string'],
            'service_plan'          => ['required', 'string'],
        ];
    }

    /**
     * @param $servicePlan
     *
     * @return mixed|null
     * @throws \Exception
     */
    public static function getServicePlan ($servicePlan): mixed
    {
        $service = null;
        db(onGetDB: function (TonicsQuery $db) use ($servicePlan, &$service) {
            $service = $db->Select("service_name, service_id, service_provider_id, others")
                ->From(TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES))
                ->WhereEquals('service_id', $servicePlan)->FetchFirst();
        });

        return $service;
    }

    /**
     * @param $providerInstanceID
     *
     * @return void
     * @throws \Exception
     * @throws Throwable
     */
    public function edit ($providerInstanceID)
    {
        self::setCurrentControllerMethod(self::EDIT_METHOD);
        $settings = [
            'instance_id' => $providerInstanceID,
            'user_id'     => \session()::getUserID(),
        ];
        $serviceInstance = self::GetServiceInstances($settings);

        if (!is_object($serviceInstance)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        view('Apps::TonicsCloud/Views/Instance/edit', [
            'InstanceData' => $serviceInstance,
            'SiteURL'      => AppConfig::getAppUrl(),
            'TimeZone'     => AppConfig::getTimeZone(),
            'FieldItems'   => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-instance-page'], (array)$serviceInstance)->getHTMLFrag(),
        ]);
    }

}