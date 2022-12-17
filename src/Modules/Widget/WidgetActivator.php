<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Widget;


use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
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

class WidgetActivator implements ExtensionConfig
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
                // RecentPostMenuWidget::class,
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
                Tables::getTable(Tables::WIDGETS) => Tables::$TABLES[Tables::WIDGETS],
                Tables::getTable(Tables::WIDGET_ITEMS) => Tables::$TABLES[Tables::WIDGET_ITEMS],
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
            "version" => '1-O-Ola.1671269391',
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

    public function onDelete(): void
    {
        // TODO: Implement onDelete() method.
    }
}