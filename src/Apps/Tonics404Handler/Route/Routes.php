<?php

namespace App\Apps\Tonics404Handler\Route;

use App\Apps\Tonics404Handler\Controller\Tonics404HandlerController;
use App\Modules\Core\Configs\AuthConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    public function routeWeb(Route $route): Route
    {

        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('tonics_404_handler/settings', [Tonics404HandlerController::class, 'index'],  alias: 'tonics404Handler.settings');
            $route->post('tonics_404_handler/settings', [Tonics404HandlerController::class, 'dataTable']);
        }, AuthConfig::getAuthRequestInterceptor());

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