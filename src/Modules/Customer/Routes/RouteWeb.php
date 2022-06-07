<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Customer\Routes;


use App\Modules\Core\RequestInterceptor\RedirectAuthenticated;
use App\Modules\Customer\Controllers\CustomerAuth\ForgotPasswordController;
use App\Modules\Customer\Controllers\CustomerAuth\LoginController;
use App\Modules\Customer\Controllers\CustomerAuth\RegisterController;
use Devsrealm\TonicsRouterSystem\Route;

trait RouteWeb
{
    /**
     * @param Route $route
     * @return Route
     * @throws \ReflectionException
     */
    public function routeWeb(Route $route): Route
    {
        $route->group('/customer',  function (Route $route){

            $route->group('', function (Route $route){
                        #---------------------------------
                    # LOGIN ROUTES...
                #---------------------------------
                $route->get('login', [LoginController::class, 'showLoginForm'], alias: 'login');
                $route->post('login', [LoginController::class, 'login']);
                $route->post('logout', [LoginController::class, 'logout'], alias: 'logout');
                        #---------------------------------
                    # REGISTRATION ROUTES...
                #---------------------------------
                $route->get('register', [RegisterController::class, 'showRegistrationForm'], alias: 'register');
                $route->post('register', [RegisterController::class, 'register']);

            }, [RedirectAuthenticated::class]);

                    #---------------------------------
                # PASSWORD RESET ROUTES...
            #---------------------------------
            $route->group('/password', callback: function (Route $route){
                $route->get('/reset', [ForgotPasswordController::class, 'showLinkRequestForm'], alias: 'request');
                $route->post('/email', [ForgotPasswordController::class, 'sendResetLinkEmail'], alias: 'email');
//                $route->get('/reset/:token', [ResetPasswordController::class, 'showResetForm'], alias: 'reset');
//                $route->post('/reset', [ResetPasswordController::class, 'CustomerAuth\ResetPasswordController@reset'], alias: 'update');
            }, alias: 'password');

                    #---------------------------------
                # EMAIL VERIFICATION...
            #---------------------------------
//            $route->get('email/verify', [VerificationController::class, 'verificationNotice'], alias: 'verification.notice');
//            $route->post('email/verify', [VerificationController::class, 'verify'], alias: 'verification.verify');

                    #---------------------------------
                # TRANSFER GUEST CUSTOMER ACCOUNT...
            #---------------------------------
//            $route->get('guest/transfer/:slugid/:guesthash', [CustomerDashboardController::class, 'transferGuestToCustomer'], alias: 'transfer.guest');

                    #---------------------------------
                # CUSTOMER DASHBOARD CONTROLLER...
            #---------------------------------
            /*$route->group('', function (Route $route) {
                $route->get('purchase/:puchaseSlugID', [CustomerDashboardController::class, 'purchaseHistory'], alias: 'purchase.history');
                $route->get('settings', [CustomerDashboardController::class, 'index'], alias: 'settings');
                $route->match(['put', 'patch'], 'settings', [CustomerDashboardController::class, 'update'], alias: 'settings.update');
                $route->get('dashboard', [CustomerDashboardController::class, 'dashboard'], alias: 'dashboard');
            }, [Customer::class, VerifyCustomer::class]);*/
        }, alias: 'customer');

        return $route;
    }

}