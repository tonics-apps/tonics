<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Customer\Routes;


use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticated;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticatedToCorrectDashboard;
use App\Modules\Customer\Controllers\CustomerAuth\ForgotPasswordController;
use App\Modules\Customer\Controllers\CustomerAuth\LoginController;
use App\Modules\Customer\Controllers\CustomerAuth\RegisterController;
use App\Modules\Customer\Controllers\DashboardController;
use App\Modules\Customer\Controllers\OrderController;
use App\Modules\Customer\RequestInterceptor\CustomerAccess;
use App\Modules\Customer\RequestInterceptor\RedirectCustomerGuest;
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
                $route->get('login', [LoginController::class, 'showLoginForm'], requestInterceptor: [RedirectAuthenticated::class], alias: 'login');
                $route->post('login', [LoginController::class, 'login']);
                $route->post('logout', [LoginController::class, 'logout'], alias: 'logout');

                        #---------------------------------
                    # REGISTRATION ROUTES...
                #---------------------------------
                $route->get('register', [RegisterController::class, 'showRegistrationForm'], alias: 'register');
                $route->post('register', [RegisterController::class, 'register']);
            });

                    #---------------------------------
                # PASSWORD RESET ROUTES...
            #---------------------------------
            $route->group('/password', callback: function (Route $route){
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

                    #---------------------------------
                # CUSTOMER DASHBOARD CONTROLLER...
            #---------------------------------
            /*$route->group('', function (Route $route) {
                $route->get('purchase/:puchaseSlugID', [CustomerDashboardController::class, 'purchaseHistory'], alias: 'purchase.history');
                $route->get('settings', [CustomerDashboardController::class, 'index'], alias: 'settings');
                $route->match(['put', 'patch'], 'settings', [CustomerDashboardController::class, 'update'], alias: 'settings.update');
                $route->get('dashboard', [CustomerDashboardController::class, 'dashboard'], alias: 'dashboard');
            }, [Customer::class, VerifyCustomer::class]);*/

            $route->group('', function (Route $route){
                        #---------------------------------
                    # CUSTOMER DASHBOARD CONTROLLER...
                #---------------------------------
                $route->get('dashboard', [DashboardController::class, 'index'], requestInterceptor: [RedirectAuthenticatedToCorrectDashboard::class], alias: 'dashboard');
                $route->get('orders', [OrderController::class, 'index'], alias: 'order.index');
                $route->get('order/audiotonics/:slug_id', [OrderController::class, 'audioTonicsPurchaseDetails'], alias: 'order.audiotonics.details');
            }, [Authenticated::class, RedirectCustomerGuest::class, CustomerAccess::class]);
        }, AuthConfig::getCSRFRequestInterceptor(),  'customer');

        return $route;
    }

}