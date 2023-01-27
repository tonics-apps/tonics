<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\RequestInterceptor\PaymentAccess;
use Devsrealm\TonicsRouterSystem\Route;

trait Routes
{

    /**
     * @throws \ReflectionException
     */
    public function routeWeb(Route $route): Route
    {
        $route->group('/admin', function (Route $route) {
            $route->group('/payment/', function (Route $route){
                $route->get('settings', [PaymentSettingsController::class, 'edit'], alias: 'payment.settings');
                $route->post('settings', [PaymentSettingsController::class, 'update']);
            });
        }, AuthConfig::getAuthRequestInterceptor([PaymentAccess::class]));

        return $route;
    }

    public function routeApi(Route $route): Route
    {
        return $route;
    }
}