<?php

namespace App\Apps\NinetySeven\Route;

use App\Apps\NinetySeven\Controller\PagesController;
use App\Apps\NinetySeven\Controller\PostsController;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route)
    {
        AppConfig::autoResolvePageRoutes(PagesController::class, $route);
        $route->get('/posts/:slug-id/:slug', [PostsController::class, 'singlePost']);
        $route->get('/categories/:slug-id/:slug', [PostsController::class, 'singleCategory']);

        return $route;
    }
}