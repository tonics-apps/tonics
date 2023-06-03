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

use App\Apps\TonicsCloud\Interfaces\CloudServerInterface;
use App\Apps\TonicsCloud\Jobs\CreateInstance;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

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
     */
    public function index()
    {

        $dataTableHeaders = [
            [
                'type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_status',
                'title' => 'Status', 'minmax' => '40px, .4fr', 'td' => 'service_instance_status'
            ],

            [
                'type' => 'select', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_status_action',
                'select_data' => 'Start, ShutDown, Reboot', 'desc' => 'Signal Command',
                'title' => 'Sig', 'minmax' => '40px, .4fr', 'td' => 'service_instance_status_action'
            ],

            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'provider_instance_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'provider_instance_id'],

            [
                'type' => 'text',
                'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'service_instance_name',
                'title' => 'Instance', 'desc' => 'Name of the instance',
                'minmax' => '85px, 1fr', 'td' => 'service_instance_name'
            ],

            ['type' => 'text', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICES . '::' . 'service_description', 'title' => 'PLan', 'desc' => 'Current Plan', 'minmax' => '50px, .5fr', 'td' => 'service_description'],

            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES . '::' . 'start_time', 'title' => 'Start Time', 'minmax' => '70px, .6fr', 'td' => 'start_time'],
        ];

        $authInfo = session()->retrieve(Session::SessionCategories_AuthInfo, jsonDecode: true);
        $data = null;
        db( onGetDB: function (TonicsQuery $db) use ($authInfo, &$data){
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $serviceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICES);

            $data = $db->Select('service_instance_status, provider_instance_id, service_instance_name, start_time, 
                CONCAT("/customer/tonics_cloud/instances/", provider_instance_id, "/edit" ) as _edit_link, service_description')
                ->From("$serviceInstanceTable")
                ->Join("$serviceTable", "$serviceInstanceTable.fk_service_id", "$serviceTable.service_id")
                ->WhereEquals('fk_customer_id', $authInfo?->user_id)->WhereNull('end_time')
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
     */
    public function dataTable(): void
    {
        $entityBag = null;
        if ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated, Reload For Changes", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500, 'An Error Occurred Updating Records');
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function create()
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
     */
    public function store()
    {
        $validator = $this->getValidator();
        $validator->changeErrorMessage(['service_plan:required' => 'Please Choose a Plan']);

        $validation = $validator->make(input()->fromPost()->all(), $this->getInstanceCreateRule());
        if ($validation->fails()){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.instances.create'));
        }

        unset($_POST['_fieldDetails']);

        $instanceJob = new CreateInstance();
        $instanceJob->setJobName('TonicsCloud_CreateContainer');
        $instanceJob->setData($_POST);
        job()->enqueue($instanceJob);

        session()->flash(['Instance Creation Enqueued, Refresh For Changes in a Minute'], [], Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsCloud.containers.index'));
    }

    /**
     * @param $providerInstanceID
     * @return void
     * @throws \Exception
     */
    public function edit($providerInstanceID)
    {
        self::setCurrentControllerMethod(self::EDIT_METHOD);
        $serviceInstance = self::getServiceInstance($providerInstanceID);

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
     * @throws \Exception
     */
    public function update($providerInstanceID)
    {
        $validator = $this->getValidator();
        $validation = $validator->make(input()->fromPost()->all(), $this->getInstanceUpdateRule());
        if ($validation->fails()){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.instances.edit', [$providerInstanceID]));
        }

        if (input()->fromPost()->hasValue('service_plan')){
            /** @var CloudServerInterface $handler */
            $handler = TonicsCloudActivator::getCloudServerHandler(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType));
            $_POST['provider_instance_id'] = $providerInstanceID;
            if ($handler->resizeInstance($_POST)){
                session()->flash(['Instance Updated Successful'], [], Session::SessionCategories_FlashMessageSuccess);
                redirect(route('tonicsCloud.instances.index'));
            }
        }

        session()->flash(['An Error Occurred Updating Instance'], input()->fromPost()->all());
        redirect(route('tonicsCloud.instances.edit', [$providerInstanceID]));
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    public function updateMultiple($entityBag): bool
    {
        $updateItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
        /** @var CloudServerInterface $handler */
        $handler = TonicsCloudActivator::getCloudServerHandler(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType));
        return $handler->changeInstanceStatus($updateItems);
    }

    /**
     * @param $entityBag
     * @return bool
     * @throws \Exception
     */
    public function deleteMultiple($entityBag): bool
    {
        $deleteItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
        /** @var CloudServerInterface $handler */
        $handler = TonicsCloudActivator::getCloudServerHandler(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType));
        return $handler->destroyInstance($deleteItems);
    }

    /**
     * @param string $instanceID
     * @return mixed|null
     * @throws \Exception
     */
    public static function getServiceInstance(string $instanceID): mixed
    {
        $serviceInstance = null;
        db(onGetDB: function (TonicsQuery $db) use ($instanceID, &$serviceInstance) {
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);
            $providersTableName = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_PROVIDER);
            /** @var CloudServerInterface $handler */
            $handler = TonicsCloudActivator::getCloudServerHandler(TonicsCloudSettingsController::getSettingsData(TonicsCloudSettingsController::CloudServerIntegrationType));
            $authInfo = session()->retrieve(Session::SessionCategories_AuthInfo, jsonDecode: true);
            $serviceInstance = $db->Select('*')->From($serviceInstanceTable)
                ->WhereEquals('fk_customer_id', $authInfo?->user_id)
                ->WhereEquals('provider_instance_id', $instanceID)->WhereNull('end_time')
                ->WhereEquals('fk_provider_id', db()->Select('provider_id')->From($providersTableName)->WhereEquals('provider_perm_name', $handler->name()))
                ->FetchFirst();
        });

        return $serviceInstance;
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