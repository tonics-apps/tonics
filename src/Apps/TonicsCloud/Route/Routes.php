<?php

namespace App\Apps\TonicsCloud\Route;

use App\Apps\TonicsCloud\Controllers\AppController;
use App\Apps\TonicsCloud\Controllers\ContainerController;
use App\Apps\TonicsCloud\Controllers\ImageController;
use App\Apps\TonicsCloud\Controllers\InstanceController;
use App\Apps\TonicsCloud\Controllers\TonicsCloudSettingsController;
use App\Apps\TonicsCloud\RequestInterceptor\TonicsCloudAccess;
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
        // WEB ROUTES
        $route->group('', function (Route $route){

                    #---------------------------------
                # TONICS CLOUD ADMIN RESOURCES...
            #---------------------------------
            $route->group('/admin/tonics_cloud', function (Route $route){

                $route->group('/images', function (Route $route){

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
                }, alias: 'images');

            }, [CoreAccess::class], 'admin');

                    #---------------------------------
                # TONICS CLOUD CUSTOMER RESOURCES...
            #---------------------------------
            $route->group('/customer/tonics_cloud', function (Route $route){

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


                        #---------------------------------
                    # CONTAINER RESOURCES...
                #---------------------------------
                $route->group('/containers', function (Route $route){
                    $route->get('', [ContainerController::class, 'index'], alias: 'index');
                    $route->post('', [ContainerController::class, 'dataTable'], alias: 'dataTables');

                    $route->post('store', [ContainerController::class, 'store']);
                    $route->get('create', [ContainerController::class, 'create'], alias: 'create');
                    $route->get(':container/edit', [ContainerController::class, 'edit'], alias: 'edit');
                    $route->match(['post', 'put'], ':container/update', [ContainerController::class, 'update']);
                }, alias: 'containers');

                        #---------------------------------
                    # APP RESOURCES...
                #---------------------------------
                $route->group('/apps', function (Route $route){
                    $route->get('', [AppController::class, 'index'], alias: 'index');
                    $route->post('', [AppController::class, 'dataTable'], alias: 'dataTables');
                }, alias: 'apps');
            }, [TonicsCloudAccess::class]);

                    #---------------------------------
                # TONICS_CLOUD SETTINGS...
            #---------------------------------
            $route->group('/admin/tonics_cloud/settings', function (Route $route){
                $route->get('', [TonicsCloudSettingsController::class, 'edit'], alias: 'settings');
                $route->post('', [TonicsCloudSettingsController::class, 'update']);
            }, [CoreAccess::class]);

        }, AuthConfig::getAuthRequestInterceptor(), 'tonicsCloud');

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