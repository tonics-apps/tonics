<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post;


use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Tables;
use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Post\EventHandlers\CategoryMenuMetaBox;
use App\Modules\Post\EventHandlers\DefaultPostCategoryFieldHandler;
use App\Modules\Post\EventHandlers\DefaultPostFieldHandler;
use App\Modules\Post\EventHandlers\HandleNewCategorySlugIDGeneration;
use App\Modules\Post\EventHandlers\HandleNewPostSlugIDGeneration;
use App\Modules\Post\EventHandlers\HandleNewPostToCategoryMapping;
use App\Modules\Post\EventHandlers\HandleUpdatePostToCategoryMapping;
use App\Modules\Post\EventHandlers\PostCategorySitemap;
use App\Modules\Post\EventHandlers\PostMenuMetaBox;
use App\Modules\Post\EventHandlers\PostMenus;
use App\Modules\Post\EventHandlers\PostSitemap;
use App\Modules\Post\Events\OnBeforePostSave;
use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Post\Events\OnPostCategoryDefaultField;
use App\Modules\Post\Events\OnPostCreate;
use App\Modules\Post\Events\OnPostDefaultField;
use App\Modules\Post\Events\OnPostUpdate;
use App\Modules\Post\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;


class PostActivator implements ExtensionConfig
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
                PostMenuMetaBox::class,
                CategoryMenuMetaBox::class
            ],

            OnAdminMenu::class => [
                PostMenus::class
            ],

            OnBeforePostSave::class => [
            ],

            OnPostCategoryCreate::class => [
                HandleNewCategorySlugIDGeneration::class
            ],

            OnPostCreate::class => [
                HandleNewPostSlugIDGeneration::class,
                HandleNewPostToCategoryMapping::class,
            ],

            OnPostUpdate::class => [
                HandleUpdatePostToCategoryMapping::class,
            ],

            OnPostDefaultField::class => [
              DefaultPostFieldHandler::class
            ],

            OnPostCategoryDefaultField::class => [
                DefaultPostCategoryFieldHandler::class
            ],

            OnAddSitemap::class => [
                PostSitemap::class,
                PostCategorySitemap::class,
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
                Tables::getTable(Tables::CATEGORIES) => Tables::$TABLES[Tables::CATEGORIES],
                Tables::getTable(Tables::POSTS) => Tables::$TABLES[Tables::POSTS],
                Tables::getTable(Tables::POST_CATEGORIES) => Tables::$TABLES[Tables::POST_CATEGORIES],
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
            "name" => "Post",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1671952498',
            "description" => "The Post Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-post-module/releases/latest",
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