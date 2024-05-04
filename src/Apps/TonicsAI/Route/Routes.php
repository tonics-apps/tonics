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