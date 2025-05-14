<?php
/*
 *     Copyright (c) 2022-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Menu\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Menu\Controllers\MenuController;
use App\Modules\Menu\Controllers\MenuControllerAPI;
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
                $route->post('', [MenuController::class, 'dataTable'], alias: 'dataTables');

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

        }, AuthConfig::getAuthRequestInterceptor([MenuAccess::class]), alias: 'menus');

        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route) {
            $route->get('/menu_items/:slug', [MenuControllerAPI::class, 'MenuItems']);
            $route->get('/menu_items_fragment/:slug', [MenuControllerAPI::class, 'MenuItemsFragment']);
        });

        return $routes;
    }

}