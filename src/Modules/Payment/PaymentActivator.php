<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Payment;


use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Library\Tables;
use Devsrealm\TonicsRouterSystem\Route;

class PaymentActivator implements ModuleConfig, PluginConfig
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
        return [];
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

    public function onInstall(): void
    {
        // TODO: Implement onInstall() method.
    }

    public function onUninstall(): void
    {
        // TODO: Implement onUninstall() method.
    }

    public function info(): array
    {
        return [
            "name" => "Payment",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1654594213',
            "description" => "The Payment Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-payment-module/releases/latest",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }
}