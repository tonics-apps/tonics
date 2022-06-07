<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Payment;


use App\Library\Tables;
use Devsrealm\TonicsRouterSystem\Route;

class PaymentActivator implements \App\Library\ModuleRegistrar\Interfaces\ModuleConfig
{

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
      /*  return [
            \App\Modules\Payment\Events\PaymentMethodsEvent::class => [
             //   \App\Modules\Payment\EventHandlers\FlutterwavePaymentSettings::class,
             //   \App\Modules\Payment\EventHandlers\PayPalPaymentSettings::class
            ],
            \App\Modules\Payment\Events\PurchaseCreatedEvent::class => [
             //   \App\Modules\Payment\EventHandlers\CreatePurchaseSlugID::class
            ]
        ];*/
    }

    /**
     * @param Route $routes
     * @return Route
     */
    public function route(Route $routes): Route
    {
       return $routes;
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return [];
    }
}