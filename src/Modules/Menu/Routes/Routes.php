<?php

namespace App\Modules\Menu\Routes;

use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\StartSession;
use App\Modules\Menu\Controllers\MenuController;
use App\Modules\Menu\Controllers\MenuControllerItems;
use App\Modules\Menu\RequestInterceptor\MenuAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        $route->group('/admin/tools', function (Route $route) {

            #---------------------------------
            # MENU RESOURCES...
            #---------------------------------
            $route->group('/menu', function (Route $route) {
                $route->get('', [MenuController::class, 'index'], alias: 'index');
                $route->post('store', [MenuController::class, 'store']);
                $route->get('create', [MenuController::class, 'create'], alias: 'create');
                $route->get(':menu/edit', [MenuController::class, 'edit'], alias: 'edit');
                $route->match(['post', 'put'], ':menu/update', [MenuController::class, 'update']);
                $route->match(['post', 'delete'], ':menu/delete', [MenuController::class, 'delete']);
                $route->match(['post', 'delete'], 'delete/multiple', [MenuController::class, 'deleteMultiple'], alias: 'deleteMultiple');
            });

            #---------------------------------
            # MENU ITEMS RESOURCES...
            #---------------------------------
            $route->group('/menu/items', function (Route $route) {
                $route->get(':menu/builder', [MenuControllerItems::class, 'index'], alias: 'index');
                $route->post('store', [MenuControllerItems::class, 'store']);
            }, alias: 'items');

        }, [StartSession::class, CSRFGuard::class, Authenticated::class, MenuAccess::class], alias: 'menus');

        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $routes): Route
    {

    }

}