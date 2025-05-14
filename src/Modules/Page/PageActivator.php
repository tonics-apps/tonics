<?php
/*
 *     Copyright (c) 2022-2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Page;


use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Commands\Module\ModuleMigrate;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use App\Modules\Page\Controllers\PagesController;
use App\Modules\Page\EventHandlers\DefaultPageFieldHandler;
use App\Modules\Page\EventHandlers\PageLayoutFields;
use App\Modules\Page\EventHandlers\PageMenu;
use App\Modules\Page\EventHandlers\PageSitemap;
use App\Modules\Page\Events\BeforePageView;
use App\Modules\Page\Events\OnPageCreated;
use App\Modules\Page\Events\OnPageDefaultField;
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
                PageMenu::class,
            ],
            OnPageDefaultField::class => [
                DefaultPageFieldHandler::class,
            ],
            OnAddSitemap::class => [
                PageSitemap::class,
            ],

            BeforePageView::class => [

            ],

            OnAddFieldSelectionDropperEvent::class => [
                PageLayoutFields::class,
            ],
        ];
    }

    /**
     * @param Route $routes
     *
     * @return Route
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function route(Route $routes): Route
    {
        if (AppConfig::TonicsIsReady()) {
            AppConfig::autoResolvePageRoutes(PagesController::class, $routes);
        }

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
            "slug_id" => "aa450ae9-2742-11ef-9736-124c30cfdb6b",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1747085600',
            "description" => "The Page Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-page-module/releases/latest",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer",
            ],
            "credits" => [],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function onUpdate(): void
    {
        self::migrateDatabases();
    }

    /**
     * @throws \ReflectionException
     */
    public static function migrateDatabases()
    {
        $appMigrate = new ModuleMigrate();
        $commandOptions = [
            '--module' => 'Page',
            '--migrate' => '',
        ];
        $appMigrate->setIsCLI(false);
        $appMigrate->run($commandOptions);
    }

    public function onDelete(): void
    {
        // TODO: Implement onDelete() method.
    }
}