<?php

namespace App\Modules\Core\Routes;

use App\Modules\Core\Controllers\Auth\CacheController;
use App\Modules\Core\Controllers\Auth\ForgotPasswordController;
use App\Modules\Core\Controllers\Auth\LoginController;
use App\Modules\Core\Controllers\DashboardController;
use App\Modules\Core\Controllers\ImportExport\ImportController;
use App\Modules\Core\Controllers\Installer;
use App\Modules\Core\Controllers\ModuleController;
use App\Modules\Core\Controllers\PluginController;
use App\Modules\Core\Controllers\ThemeController;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CoreAccess;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\InstallerChecker;
use App\Modules\Core\RequestInterceptor\ModuleAccess;
use App\Modules\Core\RequestInterceptor\PluginAccess;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticated;
use App\Modules\Core\RequestInterceptor\StartSession;
use App\Modules\Core\RequestInterceptor\ThemeAccess;
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
//                    $route->get('plugins', [PluginManagerController::class, 'index'], alias: 'plugin');
//                    $route->post('plugin/status/update', [PluginManagerController::class, 'activateDeactivate'], alias: 'plugin.status.update');

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
                }, [RedirectAuthenticated::class]);

                        #---------------------------------
                    # Registration Routes...
                #---------------------------------
                ## (No Registration for Admin, You'll Need To Register Through Customer and the Admin Can Then Change Your Role Later)
                ## $route->get('register', [RegisterController::class, 'showRegistrationForm'], [MaxAdmin::class], alias: 'register');
                ## $route->post('register', [RegisterController::class, 'register'], [MaxAdmin::class]);

                        #---------------------------------
                    # Password Reset Routes...
                #---------------------------------
                $route->get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'], alias: 'password.request');
                $route->post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'], alias: 'password.email');
//                $route->get('password/reset/:token', [ResetPasswordController::class, 'showResetForm'], alias: 'password.reset');
//                $route->post('password/reset', [ResetPasswordController::class, 'reset'], alias: 'password.update');

            }, [StartSession::class, CSRFGuard::class]);

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
                # THEME Routes...
            #---------------------------------
            $route->group('/themes', function (Route $route) {
                $route->get('', [ThemeController::class, 'index'], alias: 'index');
                $route->get(':theme/install', [ThemeController::class, 'install'], alias: 'install');
                $route->get(':theme/uninstall', [ThemeController::class, 'uninstall'], alias: 'uninstall');
                $route->match(['post', 'delete'], ':theme/delete', [ThemeController::class, 'delete']);
            }, [ThemeAccess::class], alias: 'themes');

                    #---------------------------------
                # PLUGIN Routes...
            #---------------------------------
            $route->group('/plugins', function (Route $route) {
                $route->get('', [PluginController::class, 'index'], alias: 'index');
                $route->get(':plugin/install', [PluginController::class, 'install'], alias: 'install');
                $route->get(':plugin/uninstall', [PluginController::class, 'uninstall'], alias: 'uninstall');
                $route->match(['post', 'delete'], ':plugin/delete', [PluginController::class, 'delete']);
                $route->match(['post', 'delete'], 'delete/multiple', [PluginController::class, 'deleteMultiple'], alias: 'deleteMultiple');
            }, [PluginAccess::class], alias: 'plugins');

                    #---------------------------------
                # MODULE Routes...
            #---------------------------------
            $route->group('/modules', function (Route $route) {
                $route->get('', [ModuleController::class, 'index'], alias: 'index');
            }, [ModuleAccess::class], alias: 'modules');

        }, [StartSession::class, CSRFGuard::class, Authenticated::class]);

                #---------------------------------
            # THEME AND PLUGIN ASSETS...
        #---------------------------------
        $route->group('/assets', function (Route $route){
            // you pass the path as a query string...
            $route->get('themes/:theme-name', [ThemeController::class, 'serve']);
        });

        return $route;
    }

    /**
     * @throws \ReflectionException
     */
    public function routeApi(Route $routes): Route
    {
        $routes->group('/api', function (Route $route){

//            $route->get('like/:id/:customerID/:islike', [BeatStoreController::class, 'trackLikeDislike']);
//            $route->get('store/play/:trackslugid/:playhash', [BeatStoreController::class, 'storeTrackPlaying']);

            $route->group('', function (Route $route){
                $route->post('pre-installer', [Installer::class, 'preInstall']);
                $route->get('installer', [Installer::class, 'install']);
            }, requestInterceptor: [InstallerChecker::class]);
        });
        return $routes;
    }
}