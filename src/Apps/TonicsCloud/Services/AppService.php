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

namespace App\Apps\TonicsCloud\Services;

use App\Apps\TonicsCloud\Apps\TonicsCloudACME;
use App\Apps\TonicsCloud\Apps\TonicsCloudENV;
use App\Apps\TonicsCloud\Apps\TonicsCloudHaraka;
use App\Apps\TonicsCloud\Apps\TonicsCloudMariaDB;
use App\Apps\TonicsCloud\Apps\TonicsCloudNginx;
use App\Apps\TonicsCloud\Apps\TonicsCloudPHP;
use App\Apps\TonicsCloud\Apps\TonicsCloudScript;
use App\Apps\TonicsCloud\Apps\TonicsCloudUnZip;
use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueAppHasStopped;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueAppIsRunning;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueReloadApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueStartApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueStopApp;
use App\Apps\TonicsCloud\Jobs\App\CloudJobQueueUpdateAppSettings;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\AbstractService;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class AppService extends AbstractService
{

    /**
     * @param array $data
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function updateApp(array $data = []): void
    {
        $input = input()->fromPost(empty($data) ? $_POST : $data);
        $containerID = $input->retrieve('container_id');
        $appID = $input->retrieve('app_id');

        $container = ContainerService::getContainer($containerID);
        $containerOthers = json_decode($container->containerOthers);
        $app = self::getApp($appID);
        $data = json_decode($app->others);
        if (isset($data->class) && is_a($data->class, CloudAppInterface::class, true)) {
            $fieldDetails = $input->fromPost()->retrieve('_fieldDetails');
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
                                [
                                    'job' => new CloudJobQueueReloadApp(),
                                    'children' => [
                                        [
                                            'job' => new CloudJobQueueAppIsRunning()
                                        ],
                                    ]
                                ]
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
            $this->setFails(false)->setMessage("App Changes Enqueued")->setRedirectsRoute(route('tonicsCloud.containers.apps.edit', [$containerID, $appID]));
        } else {
            $this->setFails(true)->setErrors("Class Should Be An Instance of CloudAppInterface")->setRedirectsRoute(route('tonicsCloud.containers.apps.edit', [$containerID, $appID]));
        }
    }

    /**
     * @param $container
     * @param $app
     * @param string $status
     * @return void
     * @throws \Exception
     */
    public function updateAppStatus($container, $app, string $status): void
    {
        $data = json_decode($app->others);
        $containerID = $container->container_id;
        $appID = $app->app_id;

        if ($container && $app) {

            if (isset($app->app_status)) {
                if ($app->app_status === 'Running' && $status === 'Start'){
                    return;
                }
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