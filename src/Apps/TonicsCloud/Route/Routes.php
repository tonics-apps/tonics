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

namespace App\Apps\TonicsCloud\Route;

use App\Apps\TonicsCloud\Controllers\AppController;
use App\Apps\TonicsCloud\Controllers\BillingController;
use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Controllers\DomainController;
use App\Apps\TonicsCloud\Controllers\ImageController;
use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Controllers\PaymentController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\RequestInterceptor\TonicsCloudAccess;
use App\Apps\TonicsCloud\RequestInterceptor\TonicsCloudContainerAccess;
use App\Apps\TonicsCloud\RequestInterceptor\TonicsCloudCreditAccess;
use App\Apps\TonicsCloud\RequestInterceptor\TonicsCloudDomainAccess;
use App\Apps\TonicsCloud\RequestInterceptor\TonicsCloudInstanceAccess;
use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\CoreAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        $route->head('tonics_cloud/:id', [ImageController::class, 'getHeaders'], alias: 'tonicsCloud.images.download');
        $route->get('tonics_cloud/:id', [ImageController::class, 'getHeaders'], alias: 'tonicsCloud.images.download');

        // WEB ROUTES
        $route->group('', function (Route $route){

                    #---------------------------------
                # TONICS CLOUD ADMIN RESOURCES...
            #---------------------------------
            $route->group('/admin/tonics_cloud', function (Route $route){

                $route->group('/images', function (Route $route){

                    $route->get('update_apps', [AppController::class, 'UpdateDefaultApps'], alias: 'updateApps');

                            #---------------------------------
                        # IMAGE RESOURCES...
                    #---------------------------------
                    $route->get('', [ImageController::class, 'index'], alias: 'index');
                    $route->post('', [ImageController::class, 'dataTable'], alias: 'dataTables');

                    $route->post('store', [ImageController::class, 'store']);
                    $route->get('create', [ImageController::class, 'create'], alias: 'create');

                    $route->group('', function (Route $route){
                        $route->get(':image/edit', [ImageController::class, 'edit'], alias: 'edit');
                        $route->match(['post', 'put'], ':image/update', [ImageController::class, 'update']);
                    });
                }, alias: 'admin.images');

                        #---------------------------------
                    # TONICS_CLOUD SETTINGS...
                #---------------------------------
                $route->group('/admin/tonics_cloud/settings', function (Route $route){
                    $route->get('', [TonicsCloudSettingsController::class, 'edit'], alias: 'settings');
                    $route->post('', [TonicsCloudSettingsController::class, 'update']);
                });
            }, [CoreAccess::class]);

                    #---------------------------------
                # TONICS CLOUD CUSTOMER RESOURCES...
            #---------------------------------
            $route->group('/customer/tonics_cloud', function (Route $route){

                $route->group('', function (Route $route){

                    $route->group('/instances', function (Route $route){

                                #---------------------------------
                            # INSTANCE RESOURCES...
                        #---------------------------------
                        $route->get('', [InstanceController::class, 'index'], alias: 'index');
                        $route->post('', [InstanceController::class, 'dataTable'], alias: 'dataTables');

                        $route->post('store', [InstanceController::class, 'store']);
                        $route->get('create', [InstanceController::class, 'create'], alias: 'create');

                        $route->group('', function (Route $route){
                            $route->get(':instance/edit', [InstanceController::class, 'edit'], alias: 'edit');
                            $route->match(['post', 'put'], ':instance/update', [InstanceController::class, 'update']);
                        }, [TonicsCloudInstanceAccess::class]);

                    }, alias: 'instances');

                    $route->group('/domains', function (Route $route){

                                #---------------------------------
                            # DOMAIN RESOURCES...
                        #---------------------------------
                        $route->get('', [DomainController::class, 'index'], alias: 'index');
                        $route->post('', [DomainController::class, 'dataTable'], alias: 'dataTables');

                        $route->post('store', [DomainController::class, 'store']);
                        $route->get('create', [DomainController::class, 'create'], alias: 'create');

                        $route->group('', function (Route $route){
                            $route->get(':domain/edit', [DomainController::class, 'edit'], alias: 'edit');
                            $route->match(['post', 'put'], ':domain/update', [DomainController::class, 'update']);
                        }, [TonicsCloudDomainAccess::class]);

                    }, alias: 'domains');

                            #---------------------------------
                        # CONTAINER RESOURCES...
                    #---------------------------------
                    $route->group('/containers', function (Route $route){

                        $route->get('', [ContainerController::class, 'index'], alias: 'index');
                        $route->post('', [ContainerController::class, 'dataTable'], alias: 'dataTables');

                        $route->post('store', [ContainerController::class, 'store']);
                        $route->get('create', [ContainerController::class, 'create'], alias: 'create');

                        $route->group(':container', function (Route $route){

                            $route->get('/edit', [ContainerController::class, 'edit'], alias: 'edit');
                            $route->match(['post', 'put'], '/update', [ContainerController::class, 'update']);

                            #---------------------------------
                            # APP RESOURCES...
                            #---------------------------------
                            $route->group('/apps', function (Route $route){
                                $route->get('', [AppController::class, 'index'], alias: 'index');
                                $route->post('', [AppController::class, 'dataTable'], alias: 'dataTables');

                                $route->get(':app/edit', [AppController::class, 'edit'], alias: 'edit');
                                $route->match(['post', 'put'], ':app/update', [AppController::class, 'update']);
                            }, alias: 'apps');

                        }, [TonicsCloudContainerAccess::class]);

                    }, alias: 'containers');

                }, [ TonicsCloudCreditAccess::class]);

                        #---------------------------------
                    # BILLING RESOURCES...
                #---------------------------------
                $route->group('/billings', function (Route $route) {
                    if (TonicsCloudSettingsController::billingEnabled()) {
                        $route->get('', [BillingController::class, 'billing'], alias: 'setting');
                    }
                }, alias: 'billings');

            }, [TonicsCloudAccess::class]);

        }, AuthConfig::getAuthRequestInterceptor(), 'tonicsCloud');

        $route->group('customer/tonics_cloud', function (Route $route){
            $route->group('payment', function (Route $route){
                $route->get('/get_request_flow', [PaymentController::class, 'RequestFlow']);
                $route->post('/post_request_flow', [PaymentController::class, 'RequestFlow']);
            });

        }, AuthConfig::getCSRFRequestInterceptor());

        return $route;
    }

    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route){
            // API Routes
        });
        return $routes;
    }
}