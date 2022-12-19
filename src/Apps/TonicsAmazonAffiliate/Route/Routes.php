<?php

namespace App\Apps\TonicsAmazonAffiliate\Route;

use App\Apps\TonicsAmazonAffiliate\Controller\TonicsAmazonAffiliateController;
use App\Modules\Core\Configs\AuthConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    public function routeWeb(Route $route): Route
    {
        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('tonics_amazon_affiliate/settings', [TonicsAmazonAffiliateController::class, 'edit'], alias: 'tonicsAmazonAffiliate.settings');
            $route->post('tonics_amazon_affiliate/settings', [TonicsAmazonAffiliateController::class, 'update']);
        }, AuthConfig::getAuthRequestInterceptor());
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