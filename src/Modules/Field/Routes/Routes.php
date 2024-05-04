<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

                $route->group('', function (Route $route){

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

                }, [FieldAccess::class]);

                # This doesn't have FieldAccess, meaning logged-in user can access this route, is that fine?
                $route->get('/get-field-items', [FieldController::class, 'getFieldItemsAPI'],  alias: 'getFieldItemsAPI');
            });

        }, AuthConfig::getAuthRequestInterceptor(), alias: 'fields');

        return $route;
    }


    public function routeApi(Route $routes): Route
    {
        return $routes;
    }

}