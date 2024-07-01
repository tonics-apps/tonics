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

namespace App\Modules\Customer;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Customer\EventHandlers\CustomerMenus;
use App\Modules\Customer\EventHandlers\Fields\CustomerSpamProtections;
use App\Modules\Customer\EventHandlers\SpamProtections\GlobalVariablesCheck;
use App\Modules\Customer\EventHandlers\SpamProtections\HoneyPotTrap;
use App\Modules\Customer\EventHandlers\SpamProtections\PreventDisposableEmails;
use App\Modules\Customer\Events\OnAddCustomerSpamProtectionEvent;
use App\Modules\Customer\Routes\RouteWeb;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsRouterSystem\Route;

class CustomerActivator implements ExtensionConfig
{
    use RouteWeb;

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
            OnAddCustomerSpamProtectionEvent::class => [
                HoneyPotTrap::class,
                GlobalVariablesCheck::class,
                PreventDisposableEmails::class,
            ],

            OnFieldMetaBox::class => [
                CustomerSpamProtections::class,
            ],
            OnAdminMenu::class    => [
                CustomerMenus::class,
            ],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function route (Route $routes): Route
    {
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables (): array
    {
        return
            [
                Tables::getTable(Tables::CUSTOMERS) => Tables::$TABLES[Tables::CUSTOMERS],
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
     * @throws \Throwable
     */
    public function info (): array
    {
        return [
            "name"                 => "Customer",
            "type"                 => "Module",
            "slug_id"              => "d33a73a6-273f-11ef-9736-124c30cfdb6b",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-Ola.1718718680',
            // "version" => '1-O-Ola.943905600', // fake old date
            "description"          => "The Customer Module",
            "info_url"             => '',
            "settings_page"        => route('admin.customer.settings'),
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-customer-module/releases/latest",
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