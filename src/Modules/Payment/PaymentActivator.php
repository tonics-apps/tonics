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

namespace App\Modules\Payment;


use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Payment\EventHandlers\HandleNewPurchaseSlugIDGeneration;
use App\Modules\Payment\EventHandlers\PayPal\HandleAudioTonicsPaymentCaptureCompletedEvent;
use App\Modules\Payment\Events\AudioTonics\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\OnPurchaseCreate;
use App\Modules\Payment\Events\PayPal\OnAddPayPalWebHookEvent;
use App\Modules\Payment\Events\TonicsCloud\OnAddTonicsCloudPaymentEvent;
use App\Modules\Payment\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class PaymentActivator implements ExtensionConfig
{
    use Routes;

    /**
     * @inheritDoc
     */
    public function enabled (): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function events (): array
    {
        return [

            OnAddTrackPaymentEvent::class => [

            ],

            OnAddTonicsCloudPaymentEvent::class => [

            ],

            OnPurchaseCreate::class => [
                HandleNewPurchaseSlugIDGeneration::class,
            ],

            OnAddPayPalWebHookEvent::class => [
                HandleAudioTonicsPaymentCaptureCompletedEvent::class,
            ],
        ];
    }

    /**
     * @param Route $routes
     *
     * @return Route
     * @throws \ReflectionException
     */
    public function route (Route $routes): Route
    {
        $this->routeApi($routes);
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables (): array
    {
        return [
            Tables::getTable(Tables::PURCHASES) => Tables::$TABLES[Tables::PURCHASES],
        ];
    }

    public function onInstall (): void
    {
        // TODO: Implement onInstall() method.
    }

    public function onUninstall (): void
    {
        // TODO: Implement onUninstall() method.
    }

    /**
     * @throws \Exception
     */
    public function info (): array
    {
        return [
            "name"                 => "Payment",
            "type"                 => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-Ola.1717926200',
            "description"          => "The Payment Module",
            "info_url"             => '',
            "settings_page"        => route('payment.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-payment-module/releases/latest",
            "authors"              => [
                "name"  => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }

    /**
     */
    public function onUpdate (): void
    {
        return;
    }


    public function onDelete (): void
    {
        // TODO: Implement onDelete() method.
    }
}