<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Widget;


use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Widget\EventHandlers\MenuWidgets\ImageMenuWidget;
use App\Modules\Widget\EventHandlers\MenuWidgets\PlainTextMenuWidget;
use App\Modules\Widget\EventHandlers\MenuWidgets\RecentPostMenuWidget;
use App\Modules\Widget\EventHandlers\MenuWidgets\RichTextMenuWidget;
use App\Modules\Widget\EventHandlers\WidgetMenus;
use App\Modules\Widget\Events\OnMenuWidgetMetaBox;
use App\Modules\Widget\Events\OnWidgetCreate;
use App\Modules\Widget\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class WidgetActivator implements ModuleConfig, PluginConfig
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
            OnWidgetCreate::class => [

            ],

            OnAdminMenu::class => [
                WidgetMenus::class
            ],
            OnMenuWidgetMetaBox::class => [
                RecentPostMenuWidget::class,
                ImageMenuWidget::class,
                PlainTextMenuWidget::class,
                RichTextMenuWidget::class

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
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return
            [
                Tables::getTable(Tables::WIDGETS) => Tables::getTable(Tables::WIDGETS),
                Tables::getTable(Tables::WIDGET_ITEMS) => Tables::getTable(Tables::WIDGET_ITEMS),
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
            "name" => "Widget",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1654594213',
            "description" => "The Widget Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-widget-module/releases/latest",
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