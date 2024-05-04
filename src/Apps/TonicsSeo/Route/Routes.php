<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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