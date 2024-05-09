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

namespace App\Modules\Core\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Controllers\Auth\CacheController;
use App\Modules\Core\Controllers\Auth\ForgotPasswordController;
use App\Modules\Core\Controllers\Auth\LoginController;
use App\Modules\Core\Controllers\CoreSettingsController;
use App\Modules\Core\Controllers\DashboardController;
use App\Modules\Core\Controllers\ImportExport\ImportController;
use App\Modules\Core\Controllers\Installer;
use App\Modules\Core\Controllers\AppsController;
use App\Modules\Core\Controllers\JobManagerController;
use App\Modules\Core\Controllers\OEmbedController;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CoreAccess;
use App\Modules\Core\RequestInterceptor\InstallerChecker;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticated;
use App\Modules\Core\RequestInterceptor\AppAccess;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticatedToCorrectDashboard;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {

        # experiment with table layout
        //$route->get("/table", [DashboardController::class, 'table']);

        ## For WEB
        $route->group('/admin', function (Route $route){

                    #---------------------------------
                # INSTALLER...
            #---------------------------------
            $route->get('installer', [Installer::class, 'showInstallerForm'], requestInterceptor: [InstallerChecker::class]);

            $route->group('', function (Route $route){

                $route->group('', function (Route $route){

                            #---------------------------------
                        # Core Settings
                    #---------------------------------
                    $route->group('/core/', function (Route $route){
                        $route->get('settings', [CoreSettingsController::class, 'edit'], alias: 'settings');
                        $route->post('settings', [CoreSettingsController::class, 'update']);
                    }, alias: 'core');

                            #---------------------------------
                        # DASHBOARD PANEL...
                    #---------------------------------
                    $route->get('dashboard', [DashboardController::class, 'index'], requestInterceptor: [RedirectAuthenticatedToCorrectDashboard::class], alias: 'dashboard');

                }, [Authenticated::class, CoreAccess::class]);

                        #---------------------------------
                    # Cache Clearing...
                #---------------------------------
                $route->group('/cache', function (Route $route){
                    $route->get('clear', [CacheController::class, 'clear'], alias: 'clear');
                    $route->get('warm-template', [CacheController::class, 'warmTemplateCache']);
                }, alias: 'cache');

                        #---------------------------------
                    # Authentication Routes...
                #---------------------------------
                $route->group('', function (Route $route) {
                    $route->get('login', [LoginController::class, 'showLoginForm'], requestInterceptor: [RedirectAuthenticated::class], alias: 'login');
                    $route->post('login', [LoginController::class, 'login']);
                    $route->post('logout', [LoginController::class, 'logout'], alias: 'logout');
                });

                        #---------------------------------
                    # Registration Routes...
                #---------------------------------

                        #---------------------------------
                    # Password Reset Routes...
                #---------------------------------
                $route->group('/password', callback: function (Route $route) {
                    $route->get('/reset', [ForgotPasswordController::class, 'showLinkRequestForm'], alias: 'request');
                    $route->post('/email', [ForgotPasswordController::class, 'sendResetLinkEmail'], alias: 'email');
                    $route->get('/reset/verify_email', [ForgotPasswordController::class, 'showVerifyCodeForm'], alias: 'verifyEmail');
                    $route->post('/reset/verify_email', [ForgotPasswordController::class, 'reset'], alias: 'update');
                }, alias: 'password');

            }, AuthConfig::getCSRFRequestInterceptor());

        }, alias: 'admin');

                #---------------------------------
            # THEME, PLUGINS AND IMPORT
        #---------------------------------
        $route->group('/admin/tools/', function (Route $route) {


            $route->group('', function (Route $route) {


                $route->group('/imports', function (Route $route) {
                    $route->get('', [ImportController::class, 'index'], alias: 'index');
                    $route->match(['get', 'post'], 'wordpress', [ImportController::class, 'wordpress'], alias: 'wordpress');
                    $route->match(['get'], 'wordpress-events', [ImportController::class, 'wordpressEvent'], alias: 'wordpressEvent');
                    $route->match(['get', 'post'],'beatstars', [ImportController::class, 'beatstars'], alias: 'beatstars');
                    $route->match(['get', 'post'],'airbit', [ImportController::class, 'airbit'], alias: 'airbit');
                }, alias: 'imports');

                $route->group('/job_manager', function (Route $route) {

                    $route->group('jobs', function (Route $route){
                        $route->post('', [JobManagerController::class, 'jobDataTable'],  alias: 'jobDataTable');
                        $route->get('', [JobManagerController::class, 'jobsIndex'], alias: 'jobsIndex');
                    });

                    $route->group('jobs_scheduler', function (Route $route){
                        $route->post('', [JobManagerController::class, 'jobSchedulerDataTable'],  alias: 'jobSchedulerDataTable');
                        $route->get('', [JobManagerController::class, 'jobsSchedulerIndex'], alias: 'jobsSchedulerIndex');
                    });

                },  alias: 'jobs');


            }, [CoreAccess::class]);

                    #---------------------------------
                # Apps Routes...
            #---------------------------------
            $route->group('/apps', function (Route $route) {

                $route->get('', [AppsController::class, 'index'], alias: 'index');
                $route->post('', [AppsController::class, 'dataTable'], alias: 'dataTables');

                $route->post('/install', [AppsController::class, 'install'], alias: 'install');
                $route->post('/uninstall', [AppsController::class, 'uninstall'], alias: 'uninstall');

                $route->get('/discover_updates', [AppsController::class, 'discover_updates'], alias: 'discover_updates');
                $route->get('/upload', [AppsController::class, 'uploadForm'], alias: 'uploadForm');
                $route->post('/upload', [AppsController::class, 'upload'], alias: 'upload');
            }, [AppAccess::class], alias: 'apps');

        }, AuthConfig::getAuthRequestInterceptor([Authenticated::class,]));

                #---------------------------------
            # APPS ASSETS...
        #---------------------------------
        $route->group(DriveConfig::serveAppFilePath(), function (Route $route){
            // you pass the path as a query string...
            $route->get(':app-name', [AppsController::class, 'serveAppAsset']);
        });

                #---------------------------------
            # MODULES ASSETS...
        #---------------------------------
        $route->group(DriveConfig::serveModuleFilePath(), function (Route $route){
            // you pass the path as a query string...
            $route->get(':module-name', [AppsController::class, 'serveModuleAsset']);
        });

                #---------------------------------
            # OEMBED ROUTE
        #---------------------------------
        $route->get('/services/oembed', [OEmbedController::class, 'OEmbed']);
        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route){
            $route->group('', function (Route $route){
                $route->post('pre-installer', [Installer::class, 'preInstall']);
                $route->get('installer', [Installer::class, 'install']);
            }, requestInterceptor: [InstallerChecker::class]);
        });
        return $routes;
    }
}