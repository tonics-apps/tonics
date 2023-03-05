<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page;


use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Page\Controllers\PagesController;
use App\Modules\Page\EventHandlers\DefaultPageFieldHandler;
use App\Modules\Page\EventHandlers\Fields\PageTemplateFieldSelection;
use App\Modules\Page\EventHandlers\PageMenu;
use App\Modules\Page\EventHandlers\PageSitemap;
use App\Modules\Page\Events\BeforePageView;
use App\Modules\Page\Events\OnPageCreated;
use App\Modules\Page\Events\OnPageDefaultField;
use App\Modules\Page\Events\OnPageTemplate;
use App\Modules\Page\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class PageActivator implements ExtensionConfig
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
            OnPageCreated::class => [

            ],
            OnAdminMenu::class => [
                PageMenu::class
            ],
            OnPageDefaultField::class => [
                DefaultPageFieldHandler::class
            ],
            OnAddSitemap::class => [
                PageSitemap::class
            ],

            BeforePageView::class => [

            ],

            OnPageTemplate::class => [
            ],

            OnFieldMetaBox::class => [
                PageTemplateFieldSelection::class
            ]
        ];
    }

    /**
     * @param Route $routes
     * @return Route
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function route(Route $routes): Route
    {
        AppConfig::autoResolvePageRoutes(PagesController::class, $routes);
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return
            [
                Tables::getTable(Tables::PAGES) => Tables::$TABLES[Tables::PAGES],
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
            "version" => '1-O-Ola.1678030774',
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

    public function onDelete(): void
    {
        // TODO: Implement onDelete() method.
    }
}