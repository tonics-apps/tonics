<?php

namespace App\Apps\TonicsAI\Route;

use App\Apps\TonicsAI\Controllers\TonicsAIOpenAIController;
use App\Apps\TonicsAI\Controllers\TonicsAISettingsController;
use App\Modules\Core\Configs\AuthConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    /**
     * @throws \ReflectionException
     */
    public function routeWeb(Route $route): Route
    {
        // WEB ROUTES
        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('tonics_ai/settings', [TonicsAISettingsController::class, 'edit'], alias: 'tonicsAI.settings');
            $route->post('tonics_ai/settings', [TonicsAISettingsController::class, 'update']);

            $route->group('/tonics_ai/open_ai', function (Route $route){
                $route->post('/chat/completions', [TonicsAIOpenAIController::class, 'chat']);
                $route->post('/image', [TonicsAIOpenAIController::class, 'image']);
            });
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