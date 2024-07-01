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

namespace App\Modules\Customer\Routes;


use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticated;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticatedToCorrectDashboard;
use App\Modules\Core\RequestInterceptor\RedirectToInstallerOnTonicsNotReady;
use App\Modules\Customer\Controllers\CustomerAuth\ForgotPasswordController;
use App\Modules\Customer\Controllers\CustomerAuth\LoginController;
use App\Modules\Customer\Controllers\CustomerAuth\RegisterController;
use App\Modules\Customer\Controllers\CustomerController;
use App\Modules\Customer\Controllers\CustomerSettingsController;
use App\Modules\Customer\Controllers\DashboardController;
use App\Modules\Customer\Controllers\OrderController;
use App\Modules\Customer\RequestInterceptor\CustomerAccess;
use App\Modules\Customer\RequestInterceptor\RedirectCustomerGuest;
use App\Modules\Customer\RequestInterceptor\SpamProtection;
use Devsrealm\TonicsRouterSystem\Route;

trait RouteWeb
{
    /**
     * @param Route $route
     *
     * @return Route
     * @throws \ReflectionException
     */
    public function routeWeb (Route $route): Route
    {
        $route->group('', function (Route $route) {

            $route->group('/admin', function (Route $route) {

                $route->group('/customers', function (Route $route) {
                    $route->get('', [CustomerController::class, 'index'], alias: 'index');
                    $route->post('', [CustomerController::class, 'dataTable'], alias: 'dataTables');
                }, alias: 'customers');

                #---------------------------------
                # Customer Settings
                #---------------------------------
                $route->group('/customer', function (Route $route) {

                    $route->get('settings', [CustomerSettingsController::class, 'edit'], alias: 'settings');
                    $route->post('settings', [CustomerSettingsController::class, 'update']);

                }, alias: 'customer');
            }, [Authenticated::class], alias: 'admin');

            $route->group('/customer', function (Route $route) {

                $route->group('', function (Route $route) {
                    #---------------------------------
                    # LOGIN ROUTES...
                    #---------------------------------
                    $route->get('login', [LoginController::class, 'showLoginForm'], requestInterceptor: [RedirectAuthenticated::class], alias: 'login');
                    $route->post('login', [LoginController::class, 'login']);
                    $route->post('logout', [LoginController::class, 'logout'], alias: 'logout');

                    #---------------------------------
                    # REGISTRATION ROUTES...
                    #---------------------------------
                    $route->get('register', [RegisterController::class, 'showRegistrationForm'], alias: 'register');
                    $route->post('register', [RegisterController::class, 'register'], [SpamProtection::class]);
                });

                #---------------------------------
                # PASSWORD RESET ROUTES...
                #---------------------------------
                $route->group('/password', callback: function (Route $route) {
                    $route->get('/reset', [ForgotPasswordController::class, 'showLinkRequestForm'], alias: 'request');
                    $route->post('/email', [ForgotPasswordController::class, 'sendResetLinkEmail'], alias: 'email');
                    $route->get('/reset/verify_email', [ForgotPasswordController::class, 'showVerifyCodeForm'], alias: 'verifyEmail');
                    $route->post('/reset/verify_email', [ForgotPasswordController::class, 'reset'], alias: 'update');
                }, alias: 'password');

                #---------------------------------
                # EMAIL VERIFICATION...
                #---------------------------------
                $route->get('send_verification_code', [RegisterController::class, 'sendRegisterVerificationCode'], alias: 'sendRegisterVerificationCode');
                $route->get('verifyEmail', [RegisterController::class, 'showVerifyEmailForm'], alias: 'verifyEmailForm');
                $route->post('verifyEmail', [RegisterController::class, 'verifyEmail'], alias: 'verifyEmail');

                $route->group('', function (Route $route) {

                    #---------------------------------
                    # CUSTOMER DASHBOARD CONTROLLER...
                    #---------------------------------
                    $route->get('dashboard', [DashboardController::class, 'index'], requestInterceptor: [RedirectAuthenticatedToCorrectDashboard::class], alias: 'dashboard');
                    $route->get('orders', [OrderController::class, 'index'], alias: 'order.index');
                    $route->get('order/audiotonics/:slug_id', [OrderController::class, 'audioTonicsPurchaseDetails'], alias: 'order.audiotonics.details');
                    $route->get('order/tonicscloud/:slug_id', [OrderController::class, 'tonicsCloudPurchaseDetails'], alias: 'order.tonicscloud.details');

                }, [Authenticated::class, RedirectCustomerGuest::class, CustomerAccess::class]);
            }, alias: 'customer');

        }, AuthConfig::getCSRFRequestInterceptor([RedirectToInstallerOnTonicsNotReady::class]));

        return $route;
    }

}