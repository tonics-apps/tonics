<?php

namespace App\Apps\TonicsSeo\Route;

use App\Apps\TonicsSeo\Controller\TonicsSeoController;
use App\Modules\Core\Configs\AuthConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    /**
     * @throws \ReflectionException
     */
    public function routeWeb(Route $route): Route
    {
        $route->get('ads.txt', [TonicsSeoController::class, 'ads']);
        $route->get('robots.txt', [TonicsSeoController::class, 'robots']);
        $route->get('sitemap.xml', [TonicsSeoController::class, 'sitemap']);
        $route->get('sitemap_news.xml', [TonicsSeoController::class, 'sitemapNews']);

        $route->group('/feed', function (Route $route) {
            $route->get('', [TonicsSeoController::class, 'rssHomePage']);
            $route->get('posts/category/:category-name', [TonicsSeoController::class, 'rssPostCategory']);
        });

        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('tonics_seo/settings', [TonicsSeoController::class, 'edit'], alias: 'tonicsSeo.settings');
            $route->post('tonics_seo/settings', [TonicsSeoController::class, 'update']);
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