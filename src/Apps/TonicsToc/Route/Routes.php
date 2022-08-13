<?php

namespace App\Apps\TonicsToc\Route;

use App\Apps\TonicsToc\Controller\TonicsTocController;
use App\Modules\Core\Configs\AuthConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    /**
     * @throws \ReflectionException
     */
    public function routeWeb(Route $route): Route
    {
        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('tonics_toc/settings', [TonicsTocController::class, 'edit'], alias: 'tonicsToc.settings');
            $route->post('tonics_toc/settings', [TonicsTocController::class, 'update']);
        }, AuthConfig::getAuthRequestInterceptor());
        // WEB ROUTES
        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route){
            // API Routes
        });
        return $routes;
    }
}