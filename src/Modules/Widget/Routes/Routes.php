<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Widget\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\StartSession;
use App\Modules\Menu\Controllers\MenuController;
use App\Modules\Menu\Controllers\MenuControllerItems;
use App\Modules\Menu\RequestInterceptor\MenuAccess;
use App\Modules\Widget\Controllers\WidgetController;
use App\Modules\Widget\Controllers\WidgetControllerItems;
use App\Modules\Widget\RequestInterceptor\WidgetAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        $route->group('/admin/tools', function (Route $route){

                    #---------------------------------
                # WIDGET RESOURCES...
            #---------------------------------
            $route->group('/widget', function (Route $route){
                $route->get('', [WidgetController::class, 'index'],  alias: 'index');
                $route->post('', [WidgetController::class, 'dataTable'],  alias: 'dataTables');

                $route->post('store', [WidgetController::class, 'store']);
                $route->get('create', [WidgetController::class, 'create'], alias: 'create');
                $route->get(':widget/edit', [WidgetController::class, 'edit'], alias: 'edit');
                $route->match(['post', 'put'], ':widget/update', [WidgetController::class, 'update']);
                $route->match(['post', 'delete'], ':widget/delete', [WidgetController::class, 'delete']);
                $route->match(['post', 'delete'], 'delete/multiple', [WidgetController::class, 'deleteMultiple'], alias: 'deleteMultiple');
            });

                    #---------------------------------
                # Widget ITEMS RESOURCES...
            #---------------------------------
            $route->group('/widget/items', function (Route $route){
                $route->get(':menu/builder', [WidgetControllerItems::class, 'index'],  alias: 'index');
                $route->post('store', [WidgetControllerItems::class, 'store']);
            }, alias: 'items');

        },AuthConfig::getAuthRequestInterceptor([WidgetAccess::class]), alias: 'widgets');

        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $routes): Route
    {

    }

}