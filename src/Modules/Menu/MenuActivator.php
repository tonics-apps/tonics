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

namespace App\Modules\Menu;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
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
            OnMenuMetaBox::class => [

            ],
            OnAdminMenu::class   => [
                MenuMenus::class,
            ],
            OnMenuCreate::class  => [

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
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables (): array
    {
        return
            [
                Tables::getTable(Tables::MENU_ITEM_PERMISSION) => Tables::$TABLES[Tables::MENU_ITEM_PERMISSION],
                Tables::getTable(Tables::MENU_ITEMS)           => Tables::$TABLES[Tables::MENU_ITEMS],
                Tables::getTable(Tables::MENUS)                => Tables::$TABLES[Tables::MENUS],
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

    public function info (): array
    {
        return [
            "name"                 => "Menu",
            "type"                 => "Module",
            "slug_id"              => "4d013734-2742-11ef-9736-124c30cfdb6b",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-Ola.1718095500',
            "description"          => "The Menu Module",
            "info_url"             => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-menu-module/releases/latest",
            "authors"              => [
                "name"  => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }

    public function onUpdate (): void
    {
        // TODO: Implement onUpdate() method.
    }

    public function onDelete (): void
    {
        // TODO: Implement onDelete() method.
    }
}