<?php
/*
 *     Copyright (c) 2022-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\NinetySeven\Route;

use App\Apps\NinetySeven\Controller\NinetySevenController;
use App\Apps\NinetySeven\Controller\PostsController;
use App\Apps\NinetySeven\Controller\TracksController;
use App\Modules\Core\Configs\AuthConfig;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        # For Posts
        $route->get('/posts/:slug-id/:slug', [PostsController::class, 'singlePost']);
        $route->get('/categories/:slug-id/:slug', [PostsController::class, 'singleCategory']);

        # For Tracks
        $route->get('/tracks/:slug-id/:slug', [TracksController::class, 'trackPage']);
        $route->get('/track_categories/:slug-id/:slug', [TracksController::class, 'trackCategoryPage']);
        $route->get('/audios', [TracksController::class, 'trackHome']);

        $route->group('/admin/tools/apps', function (Route $route) {
            $route->get('ninety_seven/settings', [NinetySevenController::class, 'edit'], alias: 'ninetySeven.settings');
            $route->post('ninety_seven/settings', [NinetySevenController::class, 'update']);
        }, AuthConfig::getAuthRequestInterceptor());


        return $route;
    }
}