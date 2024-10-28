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

namespace App\Modules\Post\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\PreProcessFieldDetails;
use App\Modules\Post\Controllers\PostCategoryController;
use App\Modules\Post\Controllers\PostsController;
use App\Modules\Post\RequestInterceptor\PostAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb (Route $route): Route
    {

        $route->get('posts/:id/', [PostsController::class, 'redirect']);
        $route->get('categories/:id/', [PostCategoryController::class, 'redirect']);

        ## For WEB
        $route->group('/admin/posts', function (Route $route) {

            #---------------------------------
            # POST RESOURCES...
            #---------------------------------
            $route->get('', [PostsController::class, 'index'], alias: 'index');
            $route->post('', [PostsController::class, 'dataTable'], alias: 'dataTables');

            $route->post('store', [PostsController::class, 'store'], [PreProcessFieldDetails::class]);
            $route->get('create', [PostsController::class, 'create'], alias: 'create');
            $route->get(':post/edit', [PostsController::class, 'edit'], alias: 'edit');
            $route->match(['post', 'put'], ':post/update', [PostsController::class, 'update'], [PreProcessFieldDetails::class]);
            $route->post(':post/trash', [PostsController::class, 'trash'], alias: 'trash');
            $route->post('/trash/multiple', [PostsController::class, 'trashMultiple'], alias: 'trashMultiple');
            $route->match(['post', 'delete'], ':post/delete', [PostsController::class, 'delete'], alias: 'delete');
            $route->match(['post', 'delete'], 'delete/multiple', [PostsController::class, 'deleteMultiple'], alias: 'deleteMultiple');

            #---------------------------------
            # POST CATEGORIES...
            #---------------------------------
            $route->group('/category', function (Route $route) {
                $route->get('', [PostCategoryController::class, 'index'], alias: 'index');
                $route->post('', [PostCategoryController::class, 'dataTable'], alias: 'dataTables');

                $route->get(':category/edit', [PostCategoryController::class, 'edit'], alias: 'edit');
                $route->get('create', [PostCategoryController::class, 'create'], alias: 'create');
                $route->post('store', [PostCategoryController::class, 'store'], [PreProcessFieldDetails::class]);
                $route->post(':category/trash', [PostCategoryController::class, 'trash']);
                $route->post('/trash/multiple', [PostCategoryController::class, 'trashMultiple'], alias: 'trashMultiple');
                $route->match(['post', 'put', 'patch'], ':category/update', [PostCategoryController::class, 'update'], [PreProcessFieldDetails::class]);
                $route->match(['post', 'delete'], ':category/delete', [PostCategoryController::class, 'delete']);
            }, alias: 'category');

        }, AuthConfig::getAuthRequestInterceptor([PostAccess::class]), 'posts');

        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi (Route $routes): Route
    {
        $routes->group('/api', function (Route $route) {});

        return $routes;
    }
}