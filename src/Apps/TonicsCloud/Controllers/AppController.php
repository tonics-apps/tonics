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

use App\Apps\TonicsCloud\Services\AppService;
use App\Apps\TonicsCloud\Services\ContainerService;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class AppController
{

    private FieldData $fieldData;
    private AbstractDataLayer $abstractDataLayer;
    private AppService $appService;

    /**
     * @param FieldData $fieldData
     * @param AbstractDataLayer $abstractDataLayer
     * @param AppService $appService
     */
    public function __construct(FieldData $fieldData, AbstractDataLayer $abstractDataLayer, AppService $appService)
    {
        $this->fieldData = $fieldData;
        $this->abstractDataLayer = $abstractDataLayer;
        $this->appService = $appService;
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function index($containerID)
    {
        $appsData = null;
        db(onGetDB: function (TonicsQuery $db) use ($containerID, &$appsData) {
            $appsContainersTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS_TO_CONTAINERS);
            $appTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS);
            $appsData = $db->Select("app_id, app_status, app_name, app_status_msg, app_description, 
            CONCAT('/customer/tonics_cloud/containers/$containerID/apps/', fk_app_id, '/edit' ) as _edit_link")
                ->From($appsContainersTable)
                ->Join("$appTable", "$appTable.app_id", "$appsContainersTable.fk_app_id")
                ->WhereEquals('fk_container_id', $containerID)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('app_name', url()->getParam('query'));
                })
                ->OrderByDesc(table()->pickTable($appTable, ['created_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        $dataTableHeaders = [
            [
                'type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_APPS . '::' . 'app_id',
                'title' => 'ID', 'minmax' => '20px, .2fr', 'td' => 'app_id'
            ],
            [
                'type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_APPS . '::' . 'app_status',
                'title' => 'Status', 'minmax' => '40px, .4fr', 'td' => 'app_status'
            ],

            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_APPS . '::' . 'app_name', 'title' => 'App', 'minmax' => '50px, .5fr', 'td' => 'app_name'],

            [
                'type' => 'select', 'slug' => TonicsCloudActivator::TONICS_CLOUD_APPS . '::' . 'app_status_action',
                'select_data' => 'Start, ShutDown, Reboot', 'desc' => 'Signal Command',
                'title' => 'Sig', 'minmax' => '30px, .4fr', 'td' => 'app_status_action'
            ],

            ['type' => '', 'slug' => TonicsCloudActivator::TONICS_CLOUD_APPS . '::' . 'app_status_msg',
                'title' => 'Msg',  'desc' => 'Last Message', 'minmax' => '50px, .5fr', 'td' => 'app_status_msg'],

            ['type' => '',
                'slug' => TonicsCloudActivator::TONICS_CLOUD_APPS . '::' . 'app_description',
                'title' => 'Desc', 'desc' => 'App Description', 'minmax' => '50px, .5fr', 'td' => 'app_description'
            ],
        ];

        view('Apps::TonicsCloud/Views/App/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $appsData ?? [],
                'containerID' => $containerID,
                'dataTableType' => 'TONICS_CLOUD',
            ],
            'ContainerData' => ContainerService::getContainer($containerID),
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @return void
     * @throws \Exception|\Throwable
     */
    public function dataTable($containerID)
    {
        $entityBag = null;
        if ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            response()->onSuccess([], "App Deletion is Not Supported At The Moment", more: AbstractDataLayer::DataTableEventTypeDelete);
            return;
            /*if ($this->deleteMultiple($entityBag, $containerID)) {
                response()->onSuccess([], "Records Deletion Enqueued", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }*/
        } elseif ($this->getAbstractDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->updateMultiple($entityBag, $containerID)) {
                response()->onSuccess([], "Records Update Enqueued", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500, 'An Error Occurred Updating Records');
            }
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function edit($containerID, $appID): void
    {
        $app = AppService::getApp($appID);
        $container = ContainerService::getContainer($containerID);

        db(onGetDB: function (TonicsQuery $db) use ($appID, $containerID, &$appRow) {
            $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS_TO_CONTAINERS);
            $appRow = $db->Select("*")->From($table)
                ->WhereEquals('fk_container_id', $containerID)
                ->WhereEquals('fk_app_id', $appID)
                ->FetchFirst();
        });

        $appRowOthers = json_decode($appRow->others);
        $containerOthers = json_decode($container->containerOthers, true);

        # User already have the fieldItems stored, else, boot new one.
        if (isset($appRowOthers->fieldData)){
            $fieldCategories = $this->getFieldData()->compareSortAndUpdateFieldItems(json_decode($appRowOthers->fieldData));
            $htmlFrag = $this->getFieldData()->getUsersFormFrag($fieldCategories);
        } else {
            $fieldPostData = [];
            if (isset($containerOthers['container_apps_config'][$app->app_name])){
                $fieldPostData = $containerOthers['container_apps_config'][$app->app_name];
            }
            $appOthers = json_decode($app->others);
            $htmlFrag = $this->getFieldData()
                ->generateFieldWithFieldSlug([$appOthers->field], $fieldPostData)->getHTMLFrag();
        }

        view('Apps::TonicsCloud/Views/App/edit', [
            'App' => $app,
            'ContainerID' => $containerID,
            'ContainerData' => $container,
            'SiteURL' => AppConfig::getAppUrl(),
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $htmlFrag
        ]);
    }

    /**
     * @param $containerID
     * @param $appID
     * @return void
     * @throws \Exception
     * @throws \Throwable
     */
    public function update($containerID, $appID): void
    {
        $this->appService->updateApp(input()->fromPost()->all());
        if ($this->appService->fails()) {
            session()->flash($this->appService->getErrors(), input()->fromPost()->all());
        }  else {
            session()->flash([$this->appService->getMessage()], [], Session::SessionCategories_FlashMessageSuccess);
        }
        redirect($this->appService->getRedirectsRoute());
    }

    /**
     * @param $entityBag
     * @param $containerID
     * @return bool
     * @throws \Exception
     */
    public function updateMultiple($entityBag, $containerID): bool
    {
        $container = ContainerService::getContainer($containerID);
        $updateItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
        foreach ($updateItems as $update) {
            $update = (array)$update;
            $prefix = TonicsCloudActivator::TONICS_CLOUD_APPS . '::';
            $appID = $update[$prefix . 'app_id'] ?? '';
            $currentStatus = $update[$prefix . 'app_status'] ?? '';
            $status = $update[$prefix . 'app_status_action'] ?? '';


            $app = AppService::getApp($appID);
            if (!empty($app)) {
                $app->app_status = $currentStatus;
            }
            $this->appService->updateAppStatus($container, $app, $status);


        }
        return true;
    }


    /**
     * @return AbstractDataLayer
     */
    public function getAbstractDataLayer(): AbstractDataLayer
    {
        return $this->abstractDataLayer;
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
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function UpdateDefaultApps(): void
    {
        AppService::UPDATE_DEFAULT_APPS();
        session()->flash(['App Settings Refreshed'], [], Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsCloud.admin.images.index'));
    }

}