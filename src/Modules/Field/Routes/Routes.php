<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\StartSession;
use App\Modules\Field\Controllers\FieldController;
use App\Modules\Field\Controllers\FieldControllerItems;
use App\Modules\Field\RequestInterceptor\FieldAccess;
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
                # FIELD RESOURCES...
            #---------------------------------
            $route->group('/field', function (Route $route){

                $route->get('', [FieldController::class, 'index'],  alias: 'index');
                $route->post('', [FieldController::class, 'dataTable'],  alias: 'dataTables');

                $route->post('store', [FieldController::class, 'store']);
                $route->get('create', [FieldController::class, 'create'], alias: 'create');
                $route->get(':field/edit', [FieldController::class, 'edit'], alias: 'edit');
                $route->match(['post', 'put'], ':field/update', [FieldController::class, 'update']);
                $route->match(['post', 'delete'], ':field/delete', [FieldController::class, 'delete']);
                $route->match(['post', 'delete'], 'delete/multiple', [FieldController::class, 'deleteMultiple'], alias: 'deleteMultiple');

                // for resetting field items
                $route->get('/reset-field-items', [FieldController::class, 'fieldResetItems'],  alias: 'fieldResetItems');

                        #---------------------------------
                    # field ITEMS RESOURCES...
                #---------------------------------
                $route->group('/items', function (Route $route){
                    $route->get(':field/builder', [FieldControllerItems::class, 'index'],  alias: 'index');
                    $route->post('store', [FieldControllerItems::class, 'store']);
                }, alias: 'items');

                // for post editors
                $route->match(['post', 'get'], '/selection-manager', [FieldControllerItems::class, 'fieldSelectionManager']);
                $route->post('/field-preview', [FieldControllerItems::class, 'fieldPreview']);
            });

        }, AuthConfig::getAuthRequestInterceptor([FieldAccess::class]), alias: 'fields');

        return $route;
    }


    public function routeApi(Route $routes): Route
    {
        return $routes;
    }

}