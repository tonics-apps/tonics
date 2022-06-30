<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Page;


use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Page\EventHandlers\DefaultPageFieldHandler;
use App\Modules\Page\EventHandlers\PageMenu;
use App\Modules\Page\Events\OnPageCreated;
use App\Modules\Page\Events\OnPageDefaultField;
use App\Modules\Page\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class PageActivator implements ModuleConfig, PluginConfig
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
           /* \App\Modules\Page\Events\TemplateTypeEvent::class => [
                \App\Modules\Page\EventHandlers\DefaultTemplate::class,
                \App\Modules\Page\EventHandlers\BeatStoreTemplate::class,
                \App\Modules\Page\EventHandlers\BeatStoreEnhanceTemplate::class,
                \App\Modules\Page\EventHandlers\RolaStoreTemplate::class
            ],*/
            OnPageCreated::class => [

            ],
            OnAdminMenu::class => [
                PageMenu::class
            ],
            OnPageDefaultField::class => [
                DefaultPageFieldHandler::class
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
                Tables::getTable(Tables::PAGES) => Tables::getTable(Tables::PAGES),
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
            "name" => "Page",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1654594213',
            "description" => "The Page Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-page-module/releases/latest",
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