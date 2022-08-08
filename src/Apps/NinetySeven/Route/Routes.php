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

use App\Apps\NinetySeven\Controller\PagesController;
use App\Apps\NinetySeven\Controller\PostsController;
use App\Modules\Core\Configs\AppConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        AppConfig::autoResolvePageRoutes(PagesController::class, $route);
        $route->get('/posts/:slug-id/:slug', [PostsController::class, 'singlePost']);
        $route->get('/categories/:slug-id/:slug', [PostsController::class, 'singleCategory']);

        return $route;
    }
}