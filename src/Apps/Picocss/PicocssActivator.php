<?php
/*
 *     Copyright (c) 2024-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\Picocss;

use App\Apps\Picocss\EventHandlers\AssetsHookHandler;
use App\Apps\Picocss\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\EditorsAsset;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use Devsrealm\TonicsRouterSystem\Route;

class PicocssActivator implements ExtensionConfig
{
    use Routes;

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    public function route(Route $routes): Route
    {
        $route = $this->routeApi($routes);
        return $this->routeWeb($route);
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [
            EditorsAsset::class => [
            ],

            OnHookIntoTemplate::class => [
                AssetsHookHandler::class,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function tables(): array
    {
        return [];
    }

    public function onInstall(): void
    {
        return;
    }

    public function onUninstall(): void
    {
        return;
    }

    public function onUpdate(): void
    {
        return;
    }


    public function onDelete(): void
    {
    }

    public function info(): array
    {
        return [
            "name" => "Picocss",
            "type" => "CSSFramework", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            "slug_id" => "",             // Slug ID in Tonics App Store, leave empty if not hosted on Tonics App Store
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-app.1747085600',
            "description" => "This is Picocss",
            "info_url" => '',
            "settings_page" => null, // can be null or a route name
            "update_discovery_url" => "",
            "authors" => [
                "name" => "Your Name",
                "email" => "name@website.com",
                "role" => "Developer",
            ],
            "credits" => [],
        ];
    }

}