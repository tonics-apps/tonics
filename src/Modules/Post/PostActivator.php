<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Post;


use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\EventHandlers\Field\CacheFieldIDItems;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Tables;
use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Post\EventHandlers\CategoryMenuMetaBox;
use App\Modules\Post\EventHandlers\DefaultPostFieldHandler;
use App\Modules\Post\EventHandlers\HandleNewPostSlugIDGeneration;
use App\Modules\Post\EventHandlers\HandleNewPostToCategoryMapping;
use App\Modules\Post\EventHandlers\HandleUpdatePostToCategoryMapping;
use App\Modules\Post\EventHandlers\PostMenuMetaBox;
use App\Modules\Post\EventHandlers\PostMenus;
use App\Modules\Post\Events\OnBeforePostSave;
use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Post\Events\OnPostCreate;
use App\Modules\Post\Events\OnPostDefaultField;
use App\Modules\Post\Events\OnPostUpdate;
use App\Modules\Post\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;


class PostActivator implements ModuleConfig, PluginConfig
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
                Tables::getTable(Tables::CATEGORIES) => Tables::getTable(Tables::CATEGORIES),
                Tables::getTable(Tables::CAT_RELS) => Tables::getTable(Tables::CAT_RELS),
                Tables::getTable(Tables::POSTS) => Tables::getTable(Tables::POSTS),
                Tables::getTable(Tables::POST_CATEGORIES) => Tables::getTable(Tables::POST_CATEGORIES),
                Tables::getTable(Tables::TAGS) => Tables::getTable(Tables::TAGS),
                Tables::getTable(Tables::TAG_RELS) => Tables::getTable(Tables::TAG_RELS),
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
            "version" => '1-O-Ola.1654594213',
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
}