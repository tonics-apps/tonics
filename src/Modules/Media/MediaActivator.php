<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Media;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Media\EventHandlers\MediaMenus;
use App\Modules\Media\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class MediaActivator implements ModuleConfig, PluginConfig
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
                MediaMenus::class
            ],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        $this->routeWeb($routes);
        return $this->routeApi($routes);
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return
            [
                Tables::getTable(Tables::DRIVE_BLOB_COLLATOR) => Tables::getTable(Tables::DRIVE_BLOB_COLLATOR),
                Tables::getTable(Tables::DRIVE_SYSTEM) => Tables::getTable(Tables::DRIVE_SYSTEM),
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
            "name" => "Media",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            // "version" => '1-O-Ola.1654594213',
            "version" => '1-O-Ola.943905600', // fake old date
            "description" => "The Media Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-media-module/releases/latest",
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