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

namespace App\Apps\Tonics404Handler\Route;

use App\Apps\Tonics404Handler\Controller\Tonics404HandlerController;
use App\Modules\Core\Configs\AuthConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    public function routeWeb (Route $route): Route
    {

        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('tonics_404_handler/settings', [Tonics404HandlerController::class, 'index'], alias: 'tonics404Handler.settings');
            $route->post('tonics_404_handler/settings', [Tonics404HandlerController::class, 'dataTable']);
        }, AuthConfig::getAuthRequestInterceptor());

        return $route;
    }

    public function routeApi (Route $routes): Route
    {
        $routes->group('/api', function (Route $route) {
            // API Routes
        });
        return $routes;
    }
}