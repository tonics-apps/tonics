<?php

namespace App\Modules\Track\Routes;

use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\StartSession;
use App\Modules\Track\Controllers\Artist\ArtistController;
use App\Modules\Track\Controllers\Genre\GenreController;
use App\Modules\Track\Controllers\License\LicenseController;
use App\Modules\Track\Controllers\License\LicenseControllerItems;
use App\Modules\Track\Controllers\TracksController;
use App\Modules\Track\RequestInterceptor\TrackAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        $route->get('tracks/:id/', [TracksController::class, 'redirect']);

        $route->group('/admin', function (Route $route) {

            ## FOR TRACK
            $route->group('/tracks', function (Route $route) {

                        #---------------------------------
                    # TRACK RESOURCES...
                #---------------------------------
                $route->get('', [TracksController::class, 'index'], alias: 'index');
                $route->post('store', [TracksController::class, 'store']);
                $route->get('create', [TracksController::class, 'create'], alias: 'create');
                $route->get(':track/edit', [TracksController::class, 'edit'], alias: 'edit');
                $route->match(['post', 'put'], ':track/update', [TracksController::class, 'update']);
                $route->post( '/trash/multiple', [TracksController::class, 'trashMultiple'], alias: 'trashMultiple');
                $route->post( ':track/trash', [TracksController::class, 'trash'], alias: 'trash');
                $route->match(['post', 'delete'], ':track/delete', [TracksController::class, 'delete'], alias: 'delete');
                $route->match(['post', 'delete'], 'delete/multiple', [TracksController::class, 'deleteMultiple'], alias: 'deleteMultiple');

            }, alias: 'tracks');

            ## FOR ARTIST
            $route->group('/artists', function (Route $route) {

                        #---------------------------------
                    # ARTIST RESOURCES...
                #---------------------------------
                $route->get('', [ArtistController::class, 'index'], alias: 'index');
                $route->post('store', [ArtistController::class, 'store']);
                $route->get('create', [ArtistController::class, 'create'], alias: 'create');
                $route->get(':artist/edit', [ArtistController::class, 'edit'], alias: 'edit');
                $route->match(['post', 'put'], ':artist/update', [ArtistController::class, 'update']);
                $route->match(['post', 'delete'], ':artist/delete', [ArtistController::class, 'delete'], alias: 'delete');
                $route->match(['post', 'delete'],'delete/multiple', [ArtistController::class, 'deleteMultiple'], alias: 'deleteMultiple');
            }, alias: 'artists');

            ## FOR GENRE
            $route->group('/genres', function (Route $route) {
                        #---------------------------------
                    # GENRE RESOURCES...
                #--------------------------------
                $route->get('', [GenreController::class, 'index'], alias: 'index');
                $route->post('store', [GenreController::class, 'store']);
                $route->get('create', [GenreController::class, 'create'], alias: 'create');
                $route->get(':genre/edit', [GenreController::class, 'edit'], alias: 'edit');
                $route->match(['post', 'put'], ':genre/update', [GenreController::class, 'update']);
                $route->match(['post', 'delete'], ':genre/delete', [GenreController::class, 'delete'], alias: 'delete');
                $route->match(['post', 'delete'],'delete/multiple', [GenreController::class, 'deleteMultiple'], alias: 'deleteMultiple');
        }, alias: 'genres');

            ## FOR LICENSES
            $route->group('/tools', function (Route $route) {
                        #---------------------------------
                    # LICENSE RESOURCES...
                #---------------------------------
                $route->group('/license', function (Route $route){
                    $route->get('', [LicenseController::class, 'index'],  alias: 'index');
                    $route->post('store', [LicenseController::class, 'store']);
                    $route->get('create', [LicenseController::class, 'create'], alias: 'create');
                    $route->get(':license/edit', [LicenseController::class, 'edit'], alias: 'edit');
                    $route->match(['post', 'put'], ':license/update', [LicenseController::class, 'update']);
                    $route->match(['post', 'delete'], ':license/delete', [LicenseController::class, 'delete']);
                    $route->match(['post', 'delete'], 'delete/multiple', [LicenseController::class, 'deleteMultiple'], alias: 'deleteMultiple');
                });

                        #---------------------------------
                    # LICENSE ITEMS RESOURCES...
                #---------------------------------
                $route->group('/license/items', function (Route $route){
                    $route->get(':license/builder', [LicenseControllerItems::class, 'index'],  alias: 'index');
                    $route->post('store', [LicenseControllerItems::class, 'store']);
                }, alias: 'items');
            }, alias: 'licenses');

        }, [StartSession::class, CSRFGuard::class, Authenticated::class, TrackAccess::class]);

        return $route;
    }
}