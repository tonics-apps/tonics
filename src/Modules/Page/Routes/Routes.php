<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Page\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\PreProcessFieldDetails;
use App\Modules\Page\Controllers\PagesController;
use App\Modules\Page\RequestInterceptor\PageAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        $route->group('/admin', function (Route $route) {
            ## FOR TRACK
            $route->group('/pages', function (Route $route) {

                #---------------------------------
                # TRACK RESOURCES...
                #---------------------------------
                $route->get('', [PagesController::class, 'index'], alias: 'index');
                $route->post('', [PagesController::class, 'dataTable'],  alias: 'dataTables');

                $route->post('store', [PagesController::class, 'store'], [PreProcessFieldDetails::class]);
                $route->get('create', [PagesController::class, 'create'], alias: 'create');
                $route->get(':page/edit', [PagesController::class, 'edit'], alias: 'edit');
                $route->match(['post', 'put'], ':page/update', [PagesController::class, 'update'], [PreProcessFieldDetails::class]);
                $route->post( '/trash/multiple', [PagesController::class, 'trashMultiple'], alias: 'trashMultiple');
                $route->post( ':page/trash', [PagesController::class, 'trash'], alias: 'trash');
                $route->match(['post', 'delete'], ':page/delete', [PagesController::class, 'delete'], alias: 'delete');
                $route->match(['post', 'delete'], 'delete/multiple', [PagesController::class, 'deleteMultiple'], alias: 'deleteMultiple');
            }, alias: 'pages');

        }, AuthConfig::getAuthRequestInterceptor([PageAccess::class]));

        return $route;
    }
}