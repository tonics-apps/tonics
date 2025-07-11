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

namespace App\Modules\Track\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\PreProcessFieldDetails;
use App\Modules\Track\Controllers\Artist\ArtistController;
use App\Modules\Track\Controllers\Genre\GenreController;
use App\Modules\Track\Controllers\TrackCategoryController;
use App\Modules\Track\Controllers\TracksController;
use App\Modules\Track\Controllers\TracksControllerAPI;
use App\Modules\Track\Controllers\TracksImportController;
use App\Modules\Track\Controllers\TracksPaymentController;
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
        $route->get('track_categories/:id/', [TrackCategoryController::class, 'redirect']);

        $route->group('', function (Route $route) {

            $route->group('/admin', function (Route $route) {

                ## FOR TRACK
                $route->group('/tracks', function (Route $route) {

                    #---------------------------------
                    # TRACK RESOURCES...
                    #---------------------------------
                    $route->get('', [TracksController::class, 'index'], alias: 'index');
                    $route->post('', [TracksController::class, 'dataTable'], alias: 'dataTables');

                    $route->post('store', [TracksController::class, 'store'], [PreProcessFieldDetails::class]);
                    $route->get('create', [TracksController::class, 'create'], alias: 'create');
                    $route->get(':track/edit', [TracksController::class, 'edit'], alias: 'edit');
                    $route->match(['post', 'put'], ':track/update', [TracksController::class, 'update'], [PreProcessFieldDetails::class]);
                    $route->post('/trash/multiple', [TracksController::class, 'trashMultiple'], alias: 'trashMultiple');
                    $route->post(':track/trash', [TracksController::class, 'trash'], alias: 'trash');
                    $route->match(['post', 'delete'], ':track/delete', [TracksController::class, 'delete'], alias: 'delete');
                    $route->match(['post', 'delete'], 'delete/multiple', [TracksController::class, 'deleteMultiple'], alias: 'deleteMultiple');

                    $route->get('import-track-items', [TracksImportController::class, 'importTrackItems'], alias: 'importTrackItems');
                    $route->post('import-track-items', [TracksImportController::class, 'importTrackItemsStore'], alias: 'importTrackItems');


                    #---------------------------------
                    # TRACK CATEGORIES...
                    #---------------------------------
                    $route->group('/category', function (Route $route) {
                        $route->get('', [TrackCategoryController::class, 'index'], alias: 'index');
                        $route->post('', [TrackCategoryController::class, 'dataTable'], alias: 'dataTables');

                        $route->get(':category/edit', [TrackCategoryController::class, 'edit'], alias: 'edit');
                        $route->get('create', [TrackCategoryController::class, 'create'], alias: 'create');
                        $route->post('store', [TrackCategoryController::class, 'store'], [PreProcessFieldDetails::class]);
                        $route->post(':category/trash', [TrackCategoryController::class, 'trash']);
                        $route->post('/trash/multiple', [TrackCategoryController::class, 'trashMultiple'], alias: 'trashMultiple');
                        $route->match(['post', 'put', 'patch'], ':category/update', [TrackCategoryController::class, 'update'], [PreProcessFieldDetails::class]);
                        $route->match(['post', 'delete'], ':category/delete', [TrackCategoryController::class, 'delete']);
                    }, alias: 'category');

                }, alias: 'tracks');

                ## FOR ARTIST
                $route->group('/artists', function (Route $route) {

                    #---------------------------------
                    # ARTIST RESOURCES...
                    #---------------------------------
                    $route->get('', [ArtistController::class, 'index'], alias: 'index');
                    $route->post('', [ArtistController::class, 'dataTable'], alias: 'dataTables');

                    $route->post('store', [ArtistController::class, 'store']);
                    $route->get('create', [ArtistController::class, 'create'], alias: 'create');
                    $route->get(':artist/edit', [ArtistController::class, 'edit'], alias: 'edit');
                    $route->match(['post', 'put'], ':artist/update', [ArtistController::class, 'update']);
                    $route->match(['post', 'delete'], ':artist/delete', [ArtistController::class, 'delete'], alias: 'delete');
                    $route->match(['post', 'delete'], 'delete/multiple', [ArtistController::class, 'deleteMultiple'], alias: 'deleteMultiple');
                }, alias: 'artists');

                ## FOR GENRE
                $route->group('/genres', function (Route $route) {
                    #---------------------------------
                    # GENRE RESOURCES...
                    #--------------------------------
                    $route->get('', [GenreController::class, 'index'], alias: 'index');
                    $route->post('', [GenreController::class, 'dataTable'], alias: 'dataTables');

                    $route->post('store', [GenreController::class, 'store']);
                    $route->get('create', [GenreController::class, 'create'], alias: 'create');
                    $route->get(':genre/edit', [GenreController::class, 'edit'], alias: 'edit');
                    $route->match(['post', 'put'], ':genre/update', [GenreController::class, 'update']);
                    $route->match(['post', 'delete'], ':genre/delete', [GenreController::class, 'delete'], alias: 'delete');
                    $route->match(['post', 'delete'], 'delete/multiple', [GenreController::class, 'deleteMultiple'], alias: 'deleteMultiple');
                }, alias: 'genres');

            }, [TrackAccess::class]);

        }, AuthConfig::getAuthRequestInterceptor());

        $route->group('modules/track', function (Route $route) {

            $route->group('player', function (Route $route) {
                $route->post('update_plays', [TracksController::class, 'updateTrackPlays'], alias: 'updateTrackPlays');
                $route->get('track_download', [TracksController::class, 'trackDownload'], alias: 'trackDownload');
            });

            $route->group('payment', function (Route $route) {
                $route->get('/methods', [TracksPaymentController::class, 'PaymentMethods']);
                $route->get('/get_request_flow', [TracksPaymentController::class, 'RequestFlow']);
                $route->post('/post_request_flow', [TracksPaymentController::class, 'RequestFlow']);
            });

        }, AuthConfig::getCSRFRequestInterceptor());

        return $route;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route) {

            $route->post('/tracks', [TracksControllerAPI::class, 'QueryTrack']);
            $route->get('/tracks/:slug_id', [TracksControllerAPI::class, 'TrackPageLayout']);

            $route->post('/tracks_category', [TracksControllerAPI::class, 'QueryTrackCategory']);
            $route->get('/tracks_category/:slug_id', [TracksControllerAPI::class, 'TrackCategoryPageLayout']);

        });

        // $route->get('/tracks/:slug-id/:slug', [TracksController::class, 'trackPage']);

        return $routes;
    }
}