<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Library\Tables;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\EventHandlers\CoreMenus;
use App\Modules\Core\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class CoreActivator implements ModuleConfig, PluginConfig
{
    use Routes;

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
        return [
            OnAdminMenu::class => [
                CoreMenus::class
            ]
        ];

    }

    /**
     * @param Route $routes
     * @return Route
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        $this->routeApi($routes);
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return
            [
                Tables::getTable(Tables::ADMINS) => Tables::getTable(Tables::ADMINS),
                Tables::getTable(Tables::PLUGINS) => Tables::getTable(Tables::PLUGINS),
                Tables::getTable(Tables::SESSIONS) => Tables::getTable(Tables::SESSIONS),
                Tables::getTable(Tables::USER_TYPE) => Tables::getTable(Tables::USER_TYPE),
                Tables::getTable(Tables::USERS) => Tables::getTable(Tables::USERS),
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
            "name" => "Core Module",
            "type" => "Module",
            // the first portion is when the module was created, and the second is when it was updated
            "version" => '22-06-06_22-06-06-20.51.22',
            "description" => "The Core Module",
            "update_url" => "https://github.com/tonics-apps/core-menu",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
        ];
    }
}