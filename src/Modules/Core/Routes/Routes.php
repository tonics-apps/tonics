<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Controllers\Auth\CacheController;
use App\Modules\Core\Controllers\Auth\ForgotPasswordController;
use App\Modules\Core\Controllers\Auth\LoginController;
use App\Modules\Core\Controllers\DashboardController;
use App\Modules\Core\Controllers\ImportExport\ImportController;
use App\Modules\Core\Controllers\Installer;
use App\Modules\Core\Controllers\AppsController;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CoreAccess;
use App\Modules\Core\RequestInterceptor\InstallerChecker;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticated;
use App\Modules\Core\RequestInterceptor\AppAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function routeWeb(Route $route): Route
    {
        ## For WEB
        $route->group('/admin', function (Route $route){

                    #---------------------------------
                # INSTALLER...
            #---------------------------------
            $route->get('installer', [Installer::class, 'showInstallerForm'], requestInterceptor: [InstallerChecker::class]);

            $route->group('', function (Route $route){

                $route->group('', function (Route $route){
                            #---------------------------------
                        # DASHBOARD PANEL...
                    #---------------------------------
                    $route->get('dashboard', [DashboardController::class, 'index'], alias: 'dashboard');

                            #---------------------------------
                        # For Profile Controller...
                    #---------------------------------
//                    $route->get('profile/settings', [ProfileController::class, 'index'], alias: 'profile.settings');
//                    $route->match(['put', 'patch'], 'profile/settings', [ProfileController::class, 'update'], alias: 'profile.settings.update');
//
//                    $route->get('general/settings', [ProfileController::class,  'generalSettingIndex'], alias: 'general.settings');
//                    $route->match(['put', 'patch'], 'general/settings', [ProfileController::class, 'generalSettingUpdate'], alias: 'general.update');

                }, [Authenticated::class, CoreAccess::class]);

                        #---------------------------------
                    # Cache Clearing...
                #---------------------------------
                $route->group('/cache', function (Route $route){
                    $route->get('clear', [CacheController::class, 'clear']);
                    $route->get('warm-template', [CacheController::class, 'warmTemplateCache']);
                });

                        #---------------------------------
                    # Authentication Routes...
                #---------------------------------
                $route->group('', function (Route $route){
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
                $route->get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'], alias: 'password.request');
                $route->post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'], alias: 'password.email');
                $route->get('password/reset/verify_email', [ForgotPasswordController::class, 'showVerifyCodeForm'], alias: 'password.verifyEmail');
                $route->post('password/reset/verify_email', [ForgotPasswordController::class, 'reset'], alias: 'password.update');

            }, AuthConfig::getCSRFRequestInterceptor());

        }, alias: 'admin');

                #---------------------------------
            # THEME, PLUGINS AND IMPORT
        #---------------------------------
        $route->group('/admin/tools/', function (Route $route) {
            $route->group('/imports', function (Route $route) {
                $route->get('', [ImportController::class, 'index'], alias: 'index');
                $route->match(['get', 'post'], 'wordpress', [ImportController::class, 'wordpress'], alias: 'wordpress');
                $route->match(['get'], 'wordpress-events', [ImportController::class, 'wordpressEvent'], alias: 'wordpressEvent');
                $route->match(['get', 'post'],'beatstars', [ImportController::class, 'beatstars'], alias: 'beatstars');
                $route->match(['get', 'post'],'airbit', [ImportController::class, 'airbit'], alias: 'airbit');
            }, [CoreAccess::class], alias: 'imports');

                    #---------------------------------
                # Apps Routes...
            #---------------------------------
            $route->group('/apps', function (Route $route) {
                $route->get('', [AppsController::class, 'index'], alias: 'index');
                $route->post('/install', [AppsController::class, 'install'], alias: 'install');
                $route->post('/uninstall', [AppsController::class, 'uninstall'], alias: 'uninstall');
                $route->match(['post', 'delete'], '/delete', [AppsController::class, 'delete']);

                $route->post('/discover_updates', [AppsController::class, 'discover_updates'], alias: 'discover_updates');
                $route->post('/update', [AppsController::class, 'update'], alias: 'update');
                $route->post('/upload', [AppsController::class, 'upload'], alias: 'upload');
            }, [AppAccess::class], alias: 'apps');

        }, AuthConfig::getAuthRequestInterceptor());

                #---------------------------------
            # APPS ASSETS...
        #---------------------------------
        $route->group(DriveConfig::serveAppFilePath(), function (Route $route){
            // you pass the path as a query string...
            $route->get(':app-name', [AppsController::class, 'serve']);
        });

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