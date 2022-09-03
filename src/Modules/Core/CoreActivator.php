<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core;

use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Commands\Job\JobManager;
use App\Modules\Core\Commands\OnStartUpCLI;
use App\Modules\Core\Commands\Scheduler\ScheduleManager;
use App\Modules\Core\EventHandlers\CoreMenus;
use App\Modules\Core\EventHandlers\DefaultEditorsAsset;
use App\Modules\Core\EventHandlers\Fields\Tools\Sitemap;
use App\Modules\Core\EventHandlers\JobTransporter\DatabaseJobTransporter;
use App\Modules\Core\EventHandlers\SchedulerTransporter\DatabaseSchedulerTransporter;
use App\Modules\Core\EventHandlers\TemplateEngines\DeactivateCombiningFilesInProduction;
use App\Modules\Core\EventHandlers\TemplateEngines\NativeTemplateEngine;
use App\Modules\Core\EventHandlers\TemplateEngines\WordPressTemplateEngine;
use App\Modules\Core\Events\EditorsAsset;
use App\Modules\Core\Events\OnAddJobTransporter;
use App\Modules\Core\Events\OnAddSchedulerTransporter;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Events\TonicsTemplateViewEvent\BeforeCombineModeOperation;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Routes\Routes;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsRouterSystem\Route;

class CoreActivator implements ExtensionConfig
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
            OnStartUpCLI::class => [
                ScheduleManager::class,
                JobManager::class,
            ],

            OnAddJobTransporter::class => [
                DatabaseJobTransporter::class
            ],

            OnAddSchedulerTransporter::class => [
                DatabaseSchedulerTransporter::class
            ],

            OnAdminMenu::class => [
                CoreMenus::class
            ],

            TonicsTemplateEngines::class => [
                NativeTemplateEngine::class,
                WordPressTemplateEngine::class,
            ],

            BeforeCombineModeOperation::class => [
              DeactivateCombiningFilesInProduction::class
            ],

            EditorsAsset::class => [
                DefaultEditorsAsset::class
            ],

            OnAddSitemap::class => [

            ],

            OnFieldMetaBox::class => [
              Sitemap::class
            ],

            OnHookIntoTemplate::class => [

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
                Tables::getTable(Tables::SESSIONS) => Tables::getTable(Tables::SESSIONS),
                Tables::getTable(Tables::GLOBAL) => Tables::getTable(Tables::GLOBAL),
                Tables::getTable(Tables::USERS) => Tables::getTable(Tables::USERS),
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
            "name" => "Core",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1654594213',
            "stable" => 0,
            "description" => "The Core Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-core-module/releases/latest",
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