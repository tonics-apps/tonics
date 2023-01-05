<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\Route;

use App\Apps\NinetySeven\Controller\NinetySevenController;
use App\Apps\NinetySeven\Controller\PostsController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\AuthConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        # For Posts
        $route->get('/posts/:slug-id/:slug', [PostsController::class, 'singlePost']);
        $route->get('/categories/:slug-id/:slug', [PostsController::class, 'singleCategory']);

        # For Tracks
      //  $route->get('/tracks/:slug-id/:slug', [PostsController::class, 'singlePost']);
     //   $route->get('/track_categories/:slug-id/:slug', [PostsController::class, 'singleCategory']);

        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('ninety_seven/settings', [NinetySevenController::class, 'edit'], alias: 'ninetySeven.settings');
            $route->post('ninety_seven/settings', [NinetySevenController::class, 'update']);
        }, AuthConfig::getAuthRequestInterceptor());

        return $route;
    }
}