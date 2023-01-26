<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment;


use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Commands\Module\ModuleMigrate;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsRouterSystem\Route;

class PaymentActivator implements ExtensionConfig
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
        return [
            Tables::getTable(Tables::PURCHASES) => Tables::$TABLES[Tables::PURCHASES]
        ];
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
            "version" => '1-O-Ola.1674540680',
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

    /**
     * @throws \ReflectionException
     */
    public function onUpdate(): void
    {
        self::migrateDatabases();
        return;
    }

    /**
     * @throws \ReflectionException
     */
    public static function migrateDatabases()
    {
        $appMigrate = new ModuleMigrate();
        $commandOptions = [
            '--module' => 'Payment',
            '--migrate' => '',
        ];
        $appMigrate->setIsCLI(false);
        $appMigrate->run($commandOptions);
    }


    public function onDelete(): void
    {
        // TODO: Implement onDelete() method.
    }
}