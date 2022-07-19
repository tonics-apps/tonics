<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\EventHandlers\CoreMenus;
use App\Modules\Core\EventHandlers\TemplateEngines\DeactivateCombiningFilesInProduction;
use App\Modules\Core\EventHandlers\TemplateEngines\NativeHooks;
use App\Modules\Core\EventHandlers\TemplateEngines\NativeTemplateEngine;
use App\Modules\Core\EventHandlers\TemplateEngines\WordPressTemplateEngine;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\OnSelectTonicsTemplateHooks;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Events\TonicsTemplateViewEvent\BeforeCombineModeOperation;
use App\Modules\Core\Library\Tables;
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
            ],

            TonicsTemplateEngines::class => [
                NativeTemplateEngine::class,
                WordPressTemplateEngine::class,
            ],

            OnSelectTonicsTemplateHooks::class => [
                NativeHooks::class
            ],

            BeforeCombineModeOperation::class => [
              DeactivateCombiningFilesInProduction::class
            ],
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
                Tables::getTable(Tables::SESSIONS) => Tables::getTable(Tables::SESSIONS),
                Tables::getTable(Tables::GLOBAL) => Tables::getTable(Tables::GLOBAL),
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
            "name" => "Core",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1654594213',
            "stable" => 0,
            "description" => "The Core Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-core-module/releases/latest",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }

    public function onUpdate(): void
    {
        // TODO: Implement onUpdate() method.
    }
}