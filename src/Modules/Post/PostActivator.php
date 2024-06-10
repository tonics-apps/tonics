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

namespace App\Modules\Post;


use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
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
            OnMenuMetaBox::class => [
                PostMenuMetaBox::class,
                CategoryMenuMetaBox::class,
            ],

            OnAdminMenu::class => [
                PostMenus::class,
            ],

            OnBeforePostSave::class => [
            ],

            OnPostCategoryCreate::class => [
                HandleNewCategorySlugIDGeneration::class,
            ],

            OnPostCreate::class => [
                HandleNewPostSlugIDGeneration::class,
                HandleNewPostToCategoryMapping::class,
            ],

            OnPostUpdate::class => [
                HandleUpdatePostToCategoryMapping::class,
            ],

            OnPostDefaultField::class => [
                DefaultPostFieldHandler::class,
            ],

            OnPostCategoryDefaultField::class => [
                DefaultPostCategoryFieldHandler::class,
            ],

            OnAddSitemap::class => [
                PostSitemap::class,
                PostCategorySitemap::class,
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
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables (): array
    {
        return
            [
                Tables::getTable(Tables::CATEGORIES)      => Tables::$TABLES[Tables::CATEGORIES],
                Tables::getTable(Tables::POSTS)           => Tables::$TABLES[Tables::POSTS],
                Tables::getTable(Tables::POST_CATEGORIES) => Tables::$TABLES[Tables::POST_CATEGORIES],
            ];
    }

    public function onInstall (): void
    {
        // TODO: Implement onInstall() method.
    }

    public function onUninstall (): void
    {
        // TODO: Implement onUninstall() method.
    }

    public function info (): array
    {
        return [
            "name"                 => "Post",
            "type"                 => "Module",
            "slug_id"              => "368e3a4a-2743-11ef-9736-124c30cfdb6b",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-Ola.1718095500',
            "description"          => "The Post Module",
            "info_url"             => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-post-module/releases/latest",
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
}