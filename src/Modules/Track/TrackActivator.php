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

namespace App\Modules\Track;


use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Tables;
use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Payment\EventHandlers\AudioTonicsPaymentHandler\AudioTonicsFlutterWaveHandler;
use App\Modules\Payment\EventHandlers\AudioTonicsPaymentHandler\AudioTonicsPayPalHandler;
use App\Modules\Payment\Events\AudioTonics\OnAddTrackPaymentEvent;
use App\Modules\Track\EventHandlers\Artist\HandleArtistFilterTypeCreation;
use App\Modules\Track\EventHandlers\Artist\HandleArtistFilterTypeDeletion;
use App\Modules\Track\EventHandlers\DefaultTrackCategoryFieldHandler;
use App\Modules\Track\EventHandlers\DefaultTrackFieldHandler;
use App\Modules\Track\EventHandlers\Genre\GenreMenuMetaBox;
use App\Modules\Track\EventHandlers\Genre\HandleGenreFilterTypeCreation;
use App\Modules\Track\EventHandlers\Genre\HandleGenreFilterTypeDeletion;
use App\Modules\Track\EventHandlers\Genre\HandleNewTrackToGenreMapping;
use App\Modules\Track\EventHandlers\Genre\HandleUpdateTrackToGenreMapping;
use App\Modules\Track\EventHandlers\HandleNewTrackCategorySlugIDGeneration;
use App\Modules\Track\EventHandlers\HandleNewTrackSlugIDGeneration;
use App\Modules\Track\EventHandlers\HandleNewTrackToTrackCategoryMapping;
use App\Modules\Track\EventHandlers\HandleTrackDefaultFilterMappings;
use App\Modules\Track\EventHandlers\HandleUpdateTrackToTrackCategoryMapping;
use App\Modules\Track\EventHandlers\TrackCategorySitemap;
use App\Modules\Track\EventHandlers\TrackMenuMetaBox;
use App\Modules\Track\EventHandlers\TrackMenus;
use App\Modules\Track\EventHandlers\TrackSitemap;
use App\Modules\Track\Events\Artist\OnArtistCreate;
use App\Modules\Track\Events\Artist\OnArtistDelete;
use App\Modules\Track\Events\Artist\OnArtistUpdate;
use App\Modules\Track\Events\Genres\OnGenreCreate;
use App\Modules\Track\Events\Genres\OnGenreDelete;
use App\Modules\Track\Events\Genres\OnGenreUpdate;
use App\Modules\Track\Events\OnTrackCategoryCreate;
use App\Modules\Track\Events\OnTrackCategoryDefaultField;
use App\Modules\Track\Events\OnTrackCreate;
use App\Modules\Track\Events\OnTrackDefaultField;
use App\Modules\Track\Events\OnTrackUpdate;
use App\Modules\Track\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class TrackActivator implements ExtensionConfig
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
            OnTrackCreate::class => [
                HandleNewTrackSlugIDGeneration::class,
                HandleNewTrackToGenreMapping::class,
                HandleNewTrackToTrackCategoryMapping::class,
                HandleTrackDefaultFilterMappings::class,
            ],

            OnTrackUpdate::class => [
                HandleUpdateTrackToTrackCategoryMapping::class,
                HandleUpdateTrackToGenreMapping::class,
                HandleTrackDefaultFilterMappings::class,
            ],

            OnGenreCreate::class => [
                HandleGenreFilterTypeCreation::class,
            ],

            OnGenreUpdate::class => [
                HandleGenreFilterTypeCreation::class,
            ],

            OnGenreDelete::class => [
                HandleGenreFilterTypeDeletion::class,
            ],

            OnArtistCreate::class => [
                HandleArtistFilterTypeCreation::class,
            ],

            OnArtistUpdate::class => [
                HandleArtistFilterTypeCreation::class,
            ],

            OnArtistDelete::class => [
                HandleArtistFilterTypeDeletion::class,
            ],

            OnTrackCategoryCreate::class => [
                HandleNewTrackCategorySlugIDGeneration::class,
            ],

            OnMenuMetaBox::class => [
                GenreMenuMetaBox::class,
                TrackMenuMetaBox::class,
            ],

            OnAdminMenu::class => [
                TrackMenus::class,
            ],

            OnTrackDefaultField::class => [
                DefaultTrackFieldHandler::class,
            ],

            OnTrackCategoryDefaultField::class => [
                DefaultTrackCategoryFieldHandler::class,
            ],

            OnAddSitemap::class => [
                TrackSitemap::class,
                TrackCategorySitemap::class,
            ],

            OnAddTrackPaymentEvent::class => [
                AudioTonicsPayPalHandler::class,
                AudioTonicsFlutterWaveHandler::class,
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
                Tables::getTable(Tables::ARTISTS)                => Tables::$TABLES[Tables::ARTISTS],
                Tables::getTable(Tables::GENRES)                 => Tables::$TABLES[Tables::GENRES],
                Tables::getTable(Tables::PURCHASE_TRACKS)        => Tables::$TABLES[Tables::PURCHASE_TRACKS],
                Tables::getTable(Tables::TRACKS)                 => Tables::$TABLES[Tables::TRACKS],
                Tables::getTable(Tables::TRACK_LIKES)            => Tables::$TABLES[Tables::TRACK_LIKES],
                Tables::getTable(Tables::TRACK_WISH_LIST)        => Tables::$TABLES[Tables::TRACK_WISH_LIST],
                Tables::getTable(Tables::TRACK_CATEGORIES)       => Tables::$TABLES[Tables::TRACK_CATEGORIES],
                Tables::getTable(Tables::TRACK_TRACK_CATEGORIES) => Tables::$TABLES[Tables::TRACK_TRACK_CATEGORIES],
                Tables::getTable(Tables::TRACK_GENRES)           => Tables::$TABLES[Tables::TRACK_GENRES],
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
            "name"                 => "Track",
            "type"                 => "Module",
            "slug_id"              => "6c3460ba-2743-11ef-9736-124c30cfdb6b",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-Ola.1718095500',
            "description"          => "The Track Module",
            "info_url"             => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-track-module/releases/latest",
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