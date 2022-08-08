<?php

namespace App\Apps\{{AppExample}}\Route;

use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    public function routeWeb(Route $route): Route
    {
        // WEB ROUTES
        return $route;
    }

    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route){
            // API Routes
        });
        return $routes;
    }
}