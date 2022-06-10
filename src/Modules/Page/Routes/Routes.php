<?php

namespace App\Modules\Page\Routes;

use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\StartSession;
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
                $route->post('store', [PagesController::class, 'store']);
                $route->get('create', [PagesController::class, 'create'], alias: 'create');
                $route->get(':page/edit', [PagesController::class, 'edit'], alias: 'edit');
                $route->match(['post', 'put'], ':page/update', [PagesController::class, 'update']);
                $route->post( '/trash/multiple', [PagesController::class, 'trashMultiple'], alias: 'trashMultiple');
                $route->post( ':page/trash', [PagesController::class, 'trash'], alias: 'trash');
                $route->match(['post', 'delete'], ':page/delete', [PagesController::class, 'delete'], alias: 'delete');
                $route->match(['post', 'delete'], 'delete/multiple', [PagesController::class, 'deleteMultiple'], alias: 'deleteMultiple');
            }, alias: 'pages');

        }, [StartSession::class, CSRFGuard::class, Authenticated::class, PageAccess::class]);
        return $route;
    }
}