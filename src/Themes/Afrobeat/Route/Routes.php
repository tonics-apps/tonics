<?php

namespace App\Themes\Afrobeat\Route;

use App\Modules\Core\Controllers\ThemeController;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     */
    public function routeWeb(Route $route)
    {
        $route->group('/posts', function (Route $route){
            $route->get(':slug-id/:post', [ThemeController::class, 'test']);
        });
        return $route;
    }
}