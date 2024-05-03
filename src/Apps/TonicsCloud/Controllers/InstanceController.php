<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Controllers;

use App\Apps\TonicsCloud\Interfaces\DefaultJobQueuePaths;
use App\Apps\TonicsCloud\Interfaces\QueuePathHelper;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Throwable;

class InstanceController
{
    use Validator;

    private FieldData $fieldData;
    private AbstractDataLayer $abstractDataLayer;
    private static string $currentControllerMethod = '';

    const CREATE_METHOD = 'CREATE';
    const EDIT_METHOD = 'EDIT';

    /**
     * @param FieldData $fieldData
     * @param AbstractDataLayer $abstractDataLayer
     */
    public function __construct(FieldData $fieldData, AbstractDataLayer $abstractDataLayer)
    {
        $this->fieldData = $fieldData;
        $this->abstractDataLayer = $abstractDataLayer;
    }

    /**
     * @return void
     * @throws \Exception
     * @throws Throwable
     */
    public function index(): void
    {

        $dataTableHeaders = [
            [
                'type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_status',
                'title' => 'Status', 'minmax' => '40px, .4fr', 'td' => 'service_instance_status'
            ],

            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'provider_instance_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'provider_instance_id'],

            [
                'type' => 'select', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_status_action',
                'select_data' => 'Start, ShutDown, Reboot, Terminate', 'desc' => 'Signal Command',
                'title' => 'Sig', 'minmax' => '40px, .4fr', 'td' => 'service_instance_status_action'
            ],

            [
                'type' => '',
                'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_name',
                'title' => 'Instance', 'desc' => 'Name of the instance',
                'minmax' => '55px, .6fr', 'td' => 'service_instance_name'
            ],

            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICES . '::' . 'service_description', 'title' => 'PLan', 'desc' => 'Current Plan', 'minmax' => '50px, .5fr', 'td' => 'service_description'],
        ];

        $data = null;
        db( onGetDB: function (TonicsQuery $db) use (&$data){
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $serviceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);

            $data = $db->Select('service_instance_status, provider_instance_id, service_instance_name, 
                CONCAT("/customer/tonics_cloud/instances/", provider_instance_id, "/edit" ) as _edit_link, 
                service_description')
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
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? [],
                'dataTableType' => 'TONICS_CLOUD',
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);

    }

    /**
     * @throws \Exception
     * @throws Throwable
     */
    public function dataTable(): void
    {
        $entityBag = null;
        if ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deletion Enqueued, Reload For Changes in a Minute", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Update Enqueued, Reload For Changes in a Minute", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500, 'An Error Occurred Updating Records');
            }
        }
    }

    /**
     * @throws \Exception
     * @throws Throwable
     */
    public function create(): void
    {
        self::setCurrentControllerMethod(self::CREATE_METHOD);
        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCloud/Views/Instance/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-instance-page'], $oldFormInput)->getHTMLFrag()
        ]);
    }

    /**
     * @throws \Exception
     * @throws Throwable
     */
    public function store()
    {
        $validator = $this->getValidator();
        $validator->changeErrorMessage(['service_plan:required' => 'Please Choose a Plan']);

        $validation = $validator->make(input()->fromPost()->all(), $this->getInstanceCreateRule());
        $service = InstanceController::getServicePlan(input()->fromPost()->retrieve('service_plan'));

        if ($validation->fails() || !isset($service->service_name)){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.instances.create'));
        }

        $insertedInstance = null;
        db(onGetDB: function (TonicsQuery $db) use ($service, &$insertedInstance) {
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            if (session()::getUserID() !== null){
                $insertedInstance = $db->InsertReturning($serviceInstanceTable, [
                    'service_instance_name' => input()->fromPost()->retrieve('service_instance_name'),
                    'fk_customer_id' => \session()::getUserID(), 'fk_service_id' => $service->service_id, 'fk_provider_id' => $service->service_provider_id,
                ], ['service_instance_id'], 'service_instance_id'
                );
            }
        });

        if ($insertedInstance === null){
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
     * @param $providerInstanceID
     * @return void
     * @throws \Exception
     * @throws Throwable
     */
    public function edit($providerInstanceID)
    {
        self::setCurrentControllerMethod(self::EDIT_METHOD);
        $settings = [
            'instance_id' => $providerInstanceID,
            'user_id' => \session()::getUserID()
        ];
        $serviceInstance = self::GetServiceInstances($settings);

        if (!is_object($serviceInstance)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        view('Apps::TonicsCloud/Views/Instance/edit', [
            'InstanceData' => $serviceInstance,
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-instance-page'], (array)$serviceInstance)->getHTMLFrag()
        ]);
    }

    /**
     * @param $providerInstanceID
     * @return void
     * @throws \ReflectionException
     * @throws \Exception|Throwable
     */
    public function update($providerInstanceID)
    {
        $validator = $this->getValidator();
        $validation = $validator->make(input()->fromPost()->all(), $this->getInstanceUpdateRule());
        if ($validation->fails()){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.instances.edit', [$providerInstanceID]));
        }

        # If this returns false, then user want to change the name
        if (input()->fromPost()->hasValue('service_plan')){
            if (\session()::getUserID() !== null){
                $settings = [
                    'instance_id' => $providerInstanceID,
                    'user_id' => \session()::getUserID()
                ];
                $serviceInstance = self::GetServiceInstances($settings);
                # Yh, we should never pass service_instance_id here because it might have been resized, so, provider_instance_id is enough
                $jobData = [
                    'service_plan' => input()->fromPost()->retrieve('service_plan'),
                    'service_instance_name' => $serviceInstance->service_instance_name,
                    'provider_instance_id' => $providerInstanceID,
                    'customer_id' => \session()::getUserID(),
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
     * @param $entityBag
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function updateMultiple($entityBag): bool
    {
        $updateItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
        foreach ($updateItems as $update) {
            $update = (array)$update;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::';
            $providerInstanceID = $update[$serviceInstancePrefix . 'provider_instance_id'] ?? '';
            $settings = [
                'instance_id' => $providerInstanceID,
                'user_id' => \session()::getUserID()
            ];
            $serviceInstance = self::GetServiceInstances($settings);
            $status = $update[$serviceInstancePrefix . 'service_instance_status_action'] ?? '';

            if ($serviceInstance){
                if ($serviceInstance->service_instance_status === 'Running' && $status === 'Start'){
                    continue;
                }

                if ($serviceInstance->service_instance_status === 'Offline' && $status === 'ShutDown'){
                    continue;
                }

                $jobData = [
                    'service_instance_id' => $serviceInstance->service_instance_id,
                    'provider_instance_id' => $providerInstanceID,
                    'service_instance_status_action' => $status,
                    'service_instance_status' => $serviceInstance->service_instance_status,
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
     * @param $entityBag
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function deleteMultiple($entityBag): bool
    {
        $deleteItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
        foreach ($deleteItems as $delete) {
            $delete = (array)$delete;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::';
            $providerInstanceID = $delete[$serviceInstancePrefix . 'provider_instance_id'] ?? '';
            $settings = [
                'instance_id' => $providerInstanceID,
                'user_id' => \session()::getUserID(),
            ];
            $serviceInstance = self::GetServiceInstances($settings);
            self::TerminateInstances([$serviceInstance]);
        }
        return true;
    }

    /**
     * @param array $instances
     * @return void
     * @throws \Exception
     * @throws Throwable
     */
    public static function TerminateInstances(array $instances): void
    {
        foreach ($instances as $instance)
        {
            if (isset($instance->service_instance_id)){
                $jobData = [
                    'service_instance_id' => $instance->service_instance_id,
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
     * Settings can contain
     * - `instance_id` (optional)- if this is empty, ensure `user_id` is not empty, meaning, it would retrieve instances for the user_id
     * - `column` (optional) - column to check against, defaults to `provider_instance_id`
     * - `user_id` (optional)
     * - `fetch_all` (optional) - boolean, defaults to false
     * @param array $settings
     * @return mixed|null
     * @throws Throwable
     */
    public static function GetServiceInstances(array $settings): mixed
    {
        $serviceInstances = null;
        db( onGetDB: function (TonicsQuery $db) use ($settings, &$serviceInstances) {

            $instanceID = $settings['instance_id'] ?? '';
            $column = $settings['column'] ?? 'provider_instance_id';
            $userID = $settings['user_id'] ?? '';
            $fetchAll = $settings['fetch_all'] ?? false;

            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);

            $select = "service_instance_id, provider_instance_id, service_instance_name, service_instance_status, fk_provider_id, fk_service_id, fk_customer_id, start_time, end_time, others";
            $col = table()->pick([TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES) => [$column]]);
            $db->Select($select)
                ->From($serviceInstanceTable)
                ->when($userID, function (TonicsQuery $db) use ($serviceInstanceTable, $userID) {
                    $customerTable = Tables::getTable(Tables::CUSTOMERS);
                    $db->Join($customerTable, "$customerTable.user_id", "$serviceInstanceTable.fk_customer_id");
                    $db->WhereEquals('fk_customer_id', $userID);
                })
                ->when($instanceID, function (TonicsQuery $db) use ($col, $instanceID){
                    $db->WhereEquals($col, $instanceID);
                })
                ->WhereNull('end_time');

            if ($fetchAll){
                $serviceInstances = $db->FetchResult();
            } else {
                $serviceInstances = $db->FetchFirst();
            }
        });

        return $serviceInstances;
    }

    /**
     * @param string $statusMsg
     * @param $serviceInstanceID
     * @return void
     * @throws \Exception
     */
    public static function updateContainerStatus(string $statusMsg, $serviceInstanceID): void
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
     * @param $servicePlan
     * @return mixed|null
     * @throws \Exception
     */
    public static function getServicePlan($servicePlan): mixed
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
     * @throws \Exception
     */
    public function getInstanceCreateRule(): array
    {
        return [
            'service_instance_name' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 255]],
            'cloud_region' => ['required', 'string'],
            'service_plan' => ['required', 'string']
        ];
    }

    /**
     * @throws \Exception
     */
    public function getInstanceUpdateRule(): array
    {
        return [
            'service_instance_name' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 255]],
        ];
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData(FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @return AbstractDataLayer
     */
    public function getAbstractDataLayer(): AbstractDataLayer
    {
        return $this->abstractDataLayer;
    }

    /**
     * @return string
     */
    public static function getCurrentControllerMethod(): string
    {
        return self::$currentControllerMethod;
    }

    /**
     * @param string $currentControllerMethod
     */
    public static function setCurrentControllerMethod(string $currentControllerMethod): void
    {
        self::$currentControllerMethod = $currentControllerMethod;
    }

}