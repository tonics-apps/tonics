<?php

namespace App\Themes\NinetySeven\Route;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Field\Data\FieldData;
use App\Themes\NinetySeven\Controller\PagesController;
use App\Themes\NinetySeven\Controller\PostsController;
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
        $route->group('/posts', function (Route $route) {
            $route->get(':slug-id/:post', [PostsController::class, 'singlePage']);
        });

        return $route;
    }
}