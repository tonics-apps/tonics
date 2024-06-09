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

namespace App\Modules\Core;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\Commands\Job\JobManager;
use App\Modules\Core\Commands\OnStartUpCLI;
use App\Modules\Core\Commands\Scheduler\ScheduleManager;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\EventHandlers\CoreMenus;
use App\Modules\Core\EventHandlers\DefaultEditorsAsset;
use App\Modules\Core\EventHandlers\Fields\Tools\Sitemap;
use App\Modules\Core\EventHandlers\HandleDataTableDataInTemplate;
use App\Modules\Core\EventHandlers\Hook_AddSvgSymbols;
use App\Modules\Core\EventHandlers\HookIntoAdminMenuTree;
use App\Modules\Core\EventHandlers\JobTransporter\DatabaseJobTransporter;
use App\Modules\Core\EventHandlers\Roles\CoreRolesHandler;
use App\Modules\Core\EventHandlers\SchedulerTransporter\DatabaseSchedulerTransporter;
use App\Modules\Core\EventHandlers\TemplateEngines\DeactivateCombiningFilesInProduction;
use App\Modules\Core\EventHandlers\TemplateEngines\NativeTemplateEngine;
use App\Modules\Core\EventHandlers\TemplateEngines\WordPressTemplateEngine;
use App\Modules\Core\Events\EditorsAsset;
use App\Modules\Core\Events\Licenses\OnLicenseCreate;
use App\Modules\Core\Events\OnAddConsoleCommand;
use App\Modules\Core\Events\OnAddJobTransporter;
use App\Modules\Core\Events\OnAddRole;
use App\Modules\Core\Events\OnAddSchedulerTransporter;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Events\TonicsTemplateViewEvent\BeforeCombineModeOperation;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Routes\Routes;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsRouterSystem\Route;

class CoreActivator implements ExtensionConfig, FieldItemsExtensionConfig
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
            OnStartUpCLI::class => [
                ScheduleManager::class,
                JobManager::class,
            ],

            OnAddConsoleCommand::class => [
            ],

            OnAddJobTransporter::class => [
                DatabaseJobTransporter::class,
            ],

            OnAddSchedulerTransporter::class => [
                DatabaseSchedulerTransporter::class,
            ],

            OnAdminMenu::class => [
                CoreMenus::class,
            ],

            TonicsTemplateEngines::class => [
                NativeTemplateEngine::class,
                WordPressTemplateEngine::class,
            ],

            BeforeCombineModeOperation::class => [
                DeactivateCombiningFilesInProduction::class,
            ],

            EditorsAsset::class => [
                DefaultEditorsAsset::class,
            ],

            OnAddSitemap::class => [

            ],

            OnLicenseCreate::class => [

            ],

            OnFieldMetaBox::class => [
                Sitemap::class,
            ],

            OnHookIntoTemplate::class => [
                HookIntoAdminMenuTree::class,
                HandleDataTableDataInTemplate::class,
                Hook_AddSvgSymbols::class,
            ],

            OnAddRole::class => [
                CoreRolesHandler::class,
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
        $this->routeApi($routes);
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables (): array
    {
        return
            [
                Tables::getTable(Tables::SESSIONS)         => Tables::$TABLES[Tables::SESSIONS],
                Tables::getTable(Tables::GLOBAL)           => Tables::$TABLES[Tables::GLOBAL],
                Tables::getTable(Tables::USERS)            => Tables::$TABLES[Tables::USERS],
                Tables::getTable(Tables::ROLES)            => Tables::$TABLES[Tables::ROLES],
                Tables::getTable(Tables::BROKEN_LINKS)     => Tables::$TABLES[Tables::BROKEN_LINKS],
                Tables::getTable(Tables::JOBS)             => Tables::$TABLES[Tables::JOBS],
                Tables::getTable(Tables::SCHEDULER)        => Tables::$TABLES[Tables::SCHEDULER],
                Tables::getTable(Tables::PERMISSIONS)      => Tables::$TABLES[Tables::PERMISSIONS],
                Tables::getTable(Tables::ROLE_PERMISSIONS) => Tables::$TABLES[Tables::ROLE_PERMISSIONS],
                Tables::getTable(Tables::LICENSES)         => Tables::$TABLES[Tables::LICENSES],
            ];
    }

    /**
     * @throws \Exception
     */
    public function onInstall (): void
    {
        (new FieldData())->importFieldItems(FieldConfig::DefaultFieldItems());
    }

    public function onUninstall (): void
    {
        // TODO: Implement onUninstall() method.
    }

    /**
     * @throws \Throwable
     */
    public function info (): array
    {
        return [
            "name"                 => "Core",
            "type"                 => "Module",
            // "slug_id"              => "e0a88808-1966-11ef-8fd8-00155d5e0e4f",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-Ola.1717926200',
            "stable"               => 0,
            "description"          => "The Core Module",
            "info_url"             => '',
            "settings_page"        => route('admin.core.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-core-module/releases/latest",
            "authors"              => [
                "name"  => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }

    /**
     */
    public function onUpdate (): void
    {
        return;
    }

    public function onDelete (): void
    {
        // TODO: Implement onDelete() method.
    }

    function fieldItems (): array
    {
        return FieldConfig::DefaultFieldItems();
    }
}