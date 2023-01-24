<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Menu;


use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Menu\EventHandlers\MenuMenus;
use App\Modules\Menu\Events\OnMenuCreate;
use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Menu\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class MenuActivator implements ExtensionConfig
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
            OnMenuMetaBox::class => [

            ],
            OnAdminMenu::class => [
                MenuMenus::class
            ],
            OnMenuCreate::class => [

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
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return
            [
                Tables::getTable(Tables::MENU_ITEMS) => Tables::$TABLES[Tables::MENU_ITEMS],
                Tables::getTable(Tables::MENUS) => Tables::$TABLES[Tables::MENUS],
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
            "name" => "Menu",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1674540680',
            "description" => "The Menu Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-menu-module/releases/latest",
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

    public function onDelete(): void
    {
        // TODO: Implement onDelete() method.
    }
}