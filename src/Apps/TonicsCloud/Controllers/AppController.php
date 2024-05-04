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

use App\Apps\TonicsCloud\Apps\TonicsCloudACME;
use App\Apps\TonicsCloud\Apps\TonicsCloudENV;
use App\Apps\TonicsCloud\Apps\TonicsCloudHaraka;
use App\Apps\TonicsCloud\Apps\TonicsCloudMariaDB;
use App\Apps\TonicsCloud\Apps\TonicsCloudNginx;
use App\Apps\TonicsCloud\Apps\TonicsCloudPHP;
use App\Apps\TonicsCloud\Apps\TonicsCloudScript;
use App\Apps\TonicsCloud\Apps\TonicsCloudUnZip;
use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueAppHasStopped;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueAppIsRunning;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueReloadApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueStartApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueStopApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueUpdateAppSettings;
use App\Apps\TonicsCloud\Jobs\Container\CloudJobQueueContainerIsRunning;
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
            'ContainerData' => ContainerController::getContainer($containerID),
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
            if ($this->deleteMultiple($entityBag, $containerID)) {
                response()->onSuccess([], "Records Deletion Enqueued", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
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
        $app = self::getApp($appID);
        $container = ContainerController::getContainer($containerID);

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
        $container = ContainerController::getContainer($containerID);
        $containerOthers = json_decode($container->containerOthers);
        $app = self::getApp($appID);
        $data = json_decode($app->others);
        if (isset($data->class) && is_a($data->class, CloudAppInterface::class, true)) {
            $fieldDetails = input()->fromPost()->retrieve('_fieldDetails');
            /** @var CloudAppInterface $jobObject */
            $jobObject = container()->get($data->class);
            $jobObject->setFields(json_decode($fieldDetails));
            $jobObject->setContainerReplaceableVariables($containerOthers->container_variables ?? []);
            $jobData = [
                'update_status' => false,
                'container_id' => $containerID,
                'container_variables' => $containerOthers->container_variables,
                'container_unique_slug_id' => $container->slug_id,
                'app_id' => $appID,
                'incus_container_name' => ContainerController::getIncusContainerName($container->slug_id),
                'app_class' => $data->class,
                'postFlight' => $jobObject->prepareForFlight($jobObject->getFields())
            ];

            $jobs = [
                [
                    'job' => new CloudJobQueueAppIsRunning(),
                    'children' => [
                        [
                            'job' => new CloudJobQueueUpdateAppSettings(),
                            'children' => [
                                ['job' => new CloudJobQueueReloadApp()]
                            ]
                        ]
                    ]
                ]
            ];

            db(onGetDB: function (TonicsQuery $db) use ($jobObject, $fieldDetails, $appID, $containerID) {
                $table = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS_TO_CONTAINERS);
                $db->Update($table)
                    ->Set('others', json_encode(['fieldData' => $jobObject->getFieldsToString()]))
                    ->WhereEquals('fk_container_id', $containerID)
                    ->WhereEquals('fk_app_id', $appID)
                    ->Exec();
            });

            TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
            session()->flash(['App Changes Enqueued'], [], Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsCloud.containers.apps.edit', [$containerID, $appID]));
        } else {
            throw new \Exception("Class Should Be An Instance of CloudAppInterface");
        }
    }

    /**
     * @param $entityBag
     * @param $containerID
     * @return bool
     * @throws \Exception
     */
    public function updateMultiple($entityBag, $containerID): bool
    {
        $container = ContainerController::getContainer($containerID);
        $updateItems = $this->getAbstractDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
        foreach ($updateItems as $update) {
            $update = (array)$update;
            $prefix = TonicsCloudActivator::TONICS_CLOUD_APPS . '::';
            $appID = $update[$prefix . 'app_id'] ?? '';
            $currentStatus = $update[$prefix . 'app_status'] ?? '';
            $status = $update[$prefix . 'app_status_action'] ?? '';


            $app = self::getApp($appID);
            $data = json_decode($app->others);

            if ($container && $app){
                if ($currentStatus === 'Running' && $status === 'Start'){
                    continue;
                }

                $jobData = [
                    'update_status' => false,
                    'container_id' => $containerID,
                    'app_id' => $appID,
                    'container_unique_slug_id' => $container->slug_id,
                    'incus_container_name' => ContainerController::getIncusContainerName($container->slug_id),
                    'app_class' => $data->class,
                ];

                if ($status === 'Start'){
                    $jobs = [
                        [
                            'job' => new CloudJobQueueStartApp(),
                            'children' => [
                                [
                                    'job' => new CloudJobQueueAppIsRunning()
                                ]
                            ]
                        ]
                    ];

                    TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                }

                if ($status === 'ShutDown'){
                    $jobs = [
                        [
                            'job' => new CloudJobQueueStopApp(),
                            'children' => [
                                [
                                    'job' => new CloudJobQueueAppHasStopped()
                                ]
                            ]
                        ]
                    ];

                    TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                }

                if ($status === 'Reboot'){
                    $jobs = [
                        [
                            'job' => new CloudJobQueueStopApp(),
                            'children' => [
                                [
                                    'job' => new CloudJobQueueAppHasStopped(),
                                    'children' => [
                                        [
                                            'job' => new CloudJobQueueStartApp(),
                                            'children' => [
                                                [ 'job' => new CloudJobQueueAppIsRunning() ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];

                    TonicsCloudActivator::getJobQueue()->enqueueBatch($jobs, $jobData);
                }

            }

        }
        return true;
    }

    /**
     * @throws \Exception
     */
    public static function getApp($appID)
    {
        $app = null;
        db(onGetDB: function (TonicsQuery $db) use ($appID, &$app){
            $appTable = TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS);
            $app = $db->Select('*')->From($appTable)->WhereEquals('app_id', $appID)->FetchFirst();
        });

        return $app;
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


    public static function DEFAULT_APPS(): array
    {
        $defaultConfigField = 'app-tonicscloud-app-config-default';

        return [
            [
                'app_name' => 'ACME',
                'app_description' => 'ACME(Automatically Issue and Renew Certificates), Provided By Tonics',
                'others' => json_encode(['field' => "app-tonicscloud-app-config-acme", 'class' => TonicsCloudACME::class])
            ],
            [
                'app_name' => 'ENV',
                'app_description' => 'Generates ENV for common framework, Provided By Tonics',
                'others' => json_encode(['field' => "app-tonicscloud-app-config-env", 'class' => TonicsCloudENV::class])
            ],
            [
                'app_name' => 'Nginx',
                'app_description' => 'Nginx, Provided By Tonics',
                'others' => json_encode(['field' => "app-tonicscloud-app-config-nginx", 'class' => TonicsCloudNginx::class])
            ],
            [
                'app_name' => 'PHP',
                'app_description' => 'PHP, Provided By Tonics',
                'others' => json_encode(['field' => "app-tonicscloud-app-config-php", 'class' => TonicsCloudPHP::class])
            ],
            [
                'app_name' => 'UnZip',
                'app_description' => 'UnZip, Provided By Tonics',
                'others' => json_encode(['field' => 'app-tonicscloud-app-config-upload-unzip', 'class' => TonicsCloudUnZip::class])
            ],
            [
                'app_name' => 'MariaDB',
                'app_description' => 'MariaDB, Provided By Tonics',
                'others' => json_encode(['field' => 'app-tonicscloud-app-config-mysql', 'class' => TonicsCloudMariaDB::class])
            ],
            [
                'app_name' => 'Haraka',
                'app_description' => 'Haraka, Provided By Tonics',
                'others' => json_encode(['field' => $defaultConfigField, 'class' => TonicsCloudHaraka::class])
            ],
            [
                'app_name' => 'Script',
                'app_description' => 'Script(Deployment Bash Script), Provided By Tonics',
                'others' => json_encode(['field' => "app-tonicscloud-app-config-script", 'class' => TonicsCloudScript::class])
            ],
        ];
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function UpdateDefaultApps(): void
    {
        self::UPDATE_DEFAULT_APPS();
        session()->flash(['App Settings Refreshed'], [], Session::SessionCategories_FlashMessageSuccess);
        redirect(route('tonicsCloud.admin.images.index'));
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function UPDATE_DEFAULT_APPS(): void
    {
        db(onGetDB: function ($db){
            $db->insertOnDuplicate(TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_APPS), self::DEFAULT_APPS(), ['app_description', 'others']);
        });
    }
}