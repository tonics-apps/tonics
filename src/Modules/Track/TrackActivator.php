<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track;


use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Commands\Module\ModuleMigrate;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Tables;
use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Payment\EventHandlers\TrackPaymentMethods\AudioTonicsPayPalHandler;
use App\Modules\Payment\Events\OnAddTrackPaymentEvent;
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
use App\Modules\Track\Events\OnLicenseCreate;
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
                HandleGenreFilterTypeCreation::class
            ],

            OnGenreUpdate::class => [
                HandleGenreFilterTypeCreation::class
            ],

            OnGenreDelete::class => [
                HandleGenreFilterTypeDeletion::class
            ],

            OnArtistCreate::class => [
                HandleArtistFilterTypeCreation::class
            ],

            OnArtistUpdate::class => [
                HandleArtistFilterTypeCreation::class
            ],

            OnArtistDelete::class => [
                HandleArtistFilterTypeDeletion::class
            ],

            OnLicenseCreate::class => [

            ],

            OnTrackCategoryCreate::class => [
              HandleNewTrackCategorySlugIDGeneration::class
            ],

            OnMenuMetaBox::class => [
                GenreMenuMetaBox::class,
                TrackMenuMetaBox::class
            ],

            OnAdminMenu::class => [
                TrackMenus::class
            ],

            OnTrackDefaultField::class => [
                DefaultTrackFieldHandler::class
            ],

            OnTrackCategoryDefaultField::class => [
                DefaultTrackCategoryFieldHandler::class
            ],

            OnAddSitemap::class => [
                TrackSitemap::class,
                TrackCategorySitemap::class
            ],

            OnAddTrackPaymentEvent::class => [
                AudioTonicsPayPalHandler::class,
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
                Tables::getTable(Tables::ARTISTS) => Tables::$TABLES[Tables::ARTISTS],
                Tables::getTable(Tables::GENRES) => Tables::$TABLES[Tables::GENRES],
                Tables::getTable(Tables::LICENSES) => Tables::$TABLES[Tables::LICENSES],
                Tables::getTable(Tables::PURCHASE_TRACKS) => Tables::$TABLES[Tables::PURCHASE_TRACKS],
                Tables::getTable(Tables::TRACKS) => Tables::$TABLES[Tables::TRACKS],
                Tables::getTable(Tables::TRACK_LIKES) => Tables::$TABLES[Tables::TRACK_LIKES],
                Tables::getTable(Tables::TRACK_WISH_LIST) => Tables::$TABLES[Tables::TRACK_WISH_LIST],
                Tables::getTable(Tables::TRACK_CATEGORIES) => Tables::$TABLES[Tables::TRACK_CATEGORIES],
                Tables::getTable(Tables::TRACK_TRACK_CATEGORIES) => Tables::$TABLES[Tables::TRACK_TRACK_CATEGORIES],
                Tables::getTable(Tables::TRACK_GENRES) => Tables::$TABLES[Tables::TRACK_GENRES],
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
            "name" => "Track",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1675754513',
            "description" => "The Track Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-track-module/releases/latest",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function onUpdate(): void
    {
        self::migrateDatabases();
        return;
    }

    /**
     * @throws \ReflectionException
     */
    public static function migrateDatabases()
    {
        $appMigrate = new ModuleMigrate();
        $commandOptions = [
            '--module' => 'Track',
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