<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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