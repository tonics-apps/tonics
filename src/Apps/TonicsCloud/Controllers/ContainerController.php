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

use App\Apps\TonicsCloud\Jobs\CreateContainer;
use App\Apps\TonicsCloud\Library\LXD\Client;
use App\Apps\TonicsCloud\Library\LXD\LXDHelper;
use App\Apps\TonicsCloud\Library\LXD\URL;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class ContainerController
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

    public function index()
    {

    }

    public function dataTable()
    {

    }

    /**
     * @return void
     * @throws \Exception
     */
    public function create(): void
    {
        self::setCurrentControllerMethod(self::CREATE_METHOD);
        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCloud/Views/Container/create', [
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-container-page'], $oldFormInput)->getHTMLFrag()
        ]);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function store()
    {
        $validator = $this->getValidator();
        $validator->changeErrorMessage(['cloud_instance:required' => 'Choose Instance To Place Container Into or Deploy a New One']);
        $validation = $validator->make(input()->fromPost()->all(), $this->getContainerCreateRule());
        if ($validation->fails()){
            session()->flash($validation->getErrors(), input()->fromPost()->all());
            redirect(route('tonicsCloud.containers.create'));
        }

        $cloudInstance = input()->fromPost()->retrieve('cloud_instance');
        $serviceInstanceFromDB = InstanceController::getServiceInstance($cloudInstance);
        if (empty($serviceInstanceFromDB)){
            session()->flash(["You Either Don't Own This Instance or Something Serious Went Wrong"], input()->fromPost()->all());
            redirect(route('tonicsCloud.containers.create'));
        }

        $containerReturning = null;
        db(onGetDB: function (TonicsQuery $db) use ($serviceInstanceFromDB, &$containerReturning) {
            $containerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);

            $containerImage = (int)input()->fromPost()->retrieve('container_image');
            $containerProfiles = (array)input()->fromPost()->retrieve('container_profiles', []);
            $containerName = input()->fromPost()->retrieve('container_name');
            $containerDescription = input()->fromPost()->retrieve('container_description');
            $containerDeviceConfig = input()->fromPost()->retrieve('container_devices_config');

            $containerReturning = $db->InsertReturning($containerTable, [
                'container_name' => $containerName, 'container_description' => $containerDescription, '
                service_instance_id' => $serviceInstanceFromDB->service_instance_id,
                'others' => json_encode(['container_image' => $containerImage, 'container_profiles' => $containerProfiles, 'container_device_config' => $containerDeviceConfig])
            ], ['container_id'], 'container_id');
        });

        $containerJob = new CreateContainer();
        $containerJob->setJobName('TonicsCloud_CreateContainer');
        $containerJob->setData($containerReturning);
        job()->enqueue($containerJob);

        session()->flash(['Container Creation Enqueued, Refresh For Changes in Few Seconds'], [], Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsCloud.containers.index'));
    }

    public function edit($containerID)
    {
        self::setCurrentControllerMethod(self::EDIT_METHOD);
    }

    public function update($containerID)
    {

    }

    /**
     * @throws \Exception
     */
    public function getContainerCreateRule(): array
    {
        return [
            'container_name' => ['required', 'string', 'CharLen' => ['min' => 3, 'max' => 1000]],
            'container_description' => ['required', 'string'],
            'cloud_instance' => ['required', 'string']
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