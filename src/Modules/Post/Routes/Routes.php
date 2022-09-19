<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\StartSession;
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
    public function routeWeb(Route $route): Route
    {

        $route->get('posts/:id/', [PostsController::class, 'redirect']);
        $route->get('categories/:id/', [PostCategoryController::class, 'redirect']);
        // $route->get('posts/:slug-id/:post', [PostsController::class, 'redirect']);

        ## For WEB
        $route->group('/admin/posts', function (Route $route){

                    #---------------------------------
                # POST RESOURCES...
            #---------------------------------
            $route->get('', [PostsController::class, 'index'],  alias: 'index');
            $route->post('', [PostsController::class, 'dataTable'],  alias: 'dataTables');

            $route->post('store', [PostsController::class, 'store']);
            $route->get('create', [PostsController::class, 'create'], alias: 'create');
            $route->get(':post/edit', [PostsController::class, 'edit'], alias: 'edit');
            $route->match(['post', 'put'], ':post/update', [PostsController::class, 'update']);
            $route->post( ':post/trash', [PostsController::class, 'trash'], alias: 'trash');
            $route->post( '/trash/multiple', [PostsController::class, 'trashMultiple'], alias: 'trashMultiple');
            $route->match(['post', 'delete'], ':post/delete', [PostsController::class, 'delete'], alias: 'delete');
            $route->match(['post', 'delete'], 'delete/multiple', [PostsController::class, 'deleteMultiple'], alias: 'deleteMultiple');

                    #---------------------------------
                # POST CATEGORIES...
            #---------------------------------
            $route->group('/category', function (Route $route){
                $route->get('', [PostCategoryController::class, 'index'], alias: 'index');
                $route->get(':category/edit', [PostCategoryController::class, 'edit'], alias: 'edit');
                $route->get('create', [PostCategoryController::class, 'create'], alias: 'create');
                $route->post('store', [PostCategoryController::class, 'store']);
                $route->post(':category/trash', [PostCategoryController::class, 'trash']);
                $route->post( '/trash/multiple', [PostCategoryController::class, 'trashMultiple'], alias: 'trashMultiple');
                $route->match(['post', 'put', 'patch'], ':category/update', [PostCategoryController::class, 'update']);
                $route->match(['post', 'delete'], ':category/delete', [PostCategoryController::class, 'delete']);
            }, alias: 'category');

        },AuthConfig::getAuthRequestInterceptor([PostAccess::class]), 'posts');
        
        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route){

        });

        return $routes;
    }
}