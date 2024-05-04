<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Payment\Routes;

use App\Modules\Core\Configs\AuthConfig;
use App\Modules\Payment\Controllers\OrderController;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Controllers\PayPalWebHookController;
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
                $route->get('orders', [OrderController::class, 'index'], alias: 'payment.order.index');
                $route->get('settings', [PaymentSettingsController::class, 'edit'], alias: 'payment.settings');
                $route->post('settings', [PaymentSettingsController::class, 'update']);
            });
        }, AuthConfig::getAuthRequestInterceptor([PaymentAccess::class]));


        $route->post('/payment/paypal_web_hook_endpoint', [PayPalWebHookController::class, 'handleWebHook']);
        return $route;
    }

    public function routeApi(Route $route): Route
    {
        return $route;
    }
}