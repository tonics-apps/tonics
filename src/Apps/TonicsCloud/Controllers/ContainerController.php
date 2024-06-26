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

use App\Apps\TonicsCloud\EventHandlers\Messages\TonicsCloudContainerMessage;
use App\Apps\TonicsCloud\Services\ContainerService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class ContainerController
{
    use Validator;

    const CREATE_METHOD = 'CREATE';
    const EDIT_METHOD   = 'EDIT';
    private static string     $currentControllerMethod = '';
    private FieldData         $fieldData;
    private AbstractDataLayer $abstractDataLayer;
    private ContainerService  $containerService;

    /**
     * @param FieldData $fieldData
     * @param AbstractDataLayer $abstractDataLayer
     * @param ContainerService $containerService
     */
    public function __construct (FieldData $fieldData, AbstractDataLayer $abstractDataLayer, ContainerService $containerService)
    {
        $this->fieldData = $fieldData;
        $this->abstractDataLayer = $abstractDataLayer;
        $this->containerService = $containerService;
    }

    /**
     * @param string $uuid
     *
     * @return string
     */
    public static function getIncusContainerName (string $uuid): string
    {
        return ContainerService::getIncusContainerName($uuid);
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
     * @return void
     * @throws \Exception|\Throwable
     */
    public function index (): void
    {
        $data = null;
        db(onGetDB: function (TonicsQuery $db) use (&$data) {
            $containerTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_CONTAINERS);
            $serviceInstanceTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_SERVICE_INSTANCES);

            $data = $db->Select("container_id, container_name, container_description, $serviceInstanceTable.service_instance_id, 
            $serviceInstanceTable.service_instance_name, container_status, {$this->containerService::EditLinkColumn()}, {$this->containerService::AppLinksColumn()}")
                ->From($containerTable)
                ->Join("$serviceInstanceTable", "$serviceInstanceTable.service_instance_id", "$containerTable.service_instance_id")
                ->WhereNull("$containerTable.end_time")
                ->WhereEquals("$serviceInstanceTable.fk_customer_id", \session()::getUserID())->WhereNull("$serviceInstanceTable.end_time")
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('container_name', url()->getParam('query'));
                })
                ->OrderByDesc(table()->pickTable($containerTable, ['created_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Apps::TonicsCloud/Views/Container/index', [
            'DataTable' => [
                'headers'       => $this->containerService::DataTableHeaders(),
                'paginateData'  => $data ?? [],
                'dataTableType' => 'TONICS_CLOUD',
                'controller'    => ContainerController::class,
                'messageURL'    => route('messageEvent', [TonicsCloudContainerMessage::MessageTypeKey(\session()::getUserID())]),
            ],
            'SiteURL'   => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function dataTable (): void
    {
        $entityBag = null;
        if ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->deleteMultiple($entityBag)) {
                response()->onSuccess([], "Deletion Enqueued", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        } elseif ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Update Enqueued", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500, 'An Error Occurred Updating Records');
            }
        }
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
     * @return true
     * @throws \Exception
     * @throws \Throwable
     */
    public function deleteMultiple ($entityBag): true
    {
        $deleteItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
        foreach ($deleteItems as $delete) {
            $delete = (array)$delete;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_CONTAINERS . '::';
            $containerID = $delete[$serviceInstancePrefix . 'container_id'] ?? '';
            $status = $delete[$serviceInstancePrefix . 'container_status_action'] ?? '';

            $this->containerService->deleteContainer($containerID, $status);
        }
        return true;
    }

    /**
     * @param $entityBag
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateMultiple ($entityBag): bool
    {
        $updateItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
        foreach ($updateItems as $update) {
            $update = (array)$update;
            $serviceInstancePrefix = TonicsCloudActivator::TONICS_CLOUD_CONTAINERS . '::';
            $containerID = $update[$serviceInstancePrefix . 'container_id'] ?? '';
            $status = $update[$serviceInstancePrefix . 'container_status_action'] ?? '';

            $this->containerService->updateContainerStatus($containerID, $status);

        }
        return true;
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function create (): void
    {
        self::setCurrentControllerMethod(self::CREATE_METHOD);
        $oldFormInput = \session()->retrieve(Session::SessionCategories_OldFormInput, '', true, true);
        if (!is_array($oldFormInput)) {
            $oldFormInput = [];
        }

        view('Apps::TonicsCloud/Views/Container/create', [
            'SiteURL'    => AppConfig::getAppUrl(),
            'TimeZone'   => AppConfig::getTimeZone(),
            'FieldItems' => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-container-page'], $oldFormInput)->getHTMLFrag(),
        ]);
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
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Throwable
     */
    public function store ()
    {
        $this->containerService->createContainer(input()->fromPost()->all());
        if ($this->containerService->fails()) {
            session()->flash($this->containerService->getErrors(), input()->fromPost()->all());
        } else {
            session()->flash([$this->containerService->getMessage()], [], Session::SessionCategories_FlashMessageSuccess);
        }
        redirect($this->containerService->getRedirectsRoute());
    }

    /**
     * @param $containerID
     *
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function edit ($containerID): void
    {
        self::setCurrentControllerMethod(self::EDIT_METHOD);
        $container = ContainerService::getContainer($containerID);

        if (!is_object($container)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
        }

        $containerOthers = json_decode($container->containerOthers, true);
        $container = [...(array)$container, ...$containerOthers];

        view('Apps::TonicsCloud/Views/Container/edit', [
            'ContainerData' => $container,
            'SiteURL'       => AppConfig::getAppUrl(),
            'TimeZone'      => AppConfig::getTimeZone(),
            'FieldItems'    => $this->getFieldData()
                ->generateFieldWithFieldSlug(['app-tonicscloud-container-page'], $container)->getHTMLFrag(),
        ]);
    }

    /**d
     *
     * @param $containerID
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Throwable
     */
    public function update ($containerID)
    {
        $this->containerService->updateContainer(input()->fromPost()->all());
        if ($this->containerService->fails()) {
            session()->flash($this->containerService->getErrors(), input()->fromPost()->all());
        } else {
            session()->flash([$this->containerService->getMessage()], [], Session::SessionCategories_FlashMessageSuccess);
        }
        redirect($this->containerService->getRedirectsRoute());
    }
}