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