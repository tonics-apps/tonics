<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track;


use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Library\Tables;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Track\EventHandlers\DefaultTrackFieldHandler;
use App\Modules\Track\EventHandlers\GenreMenuMetaBox;
use App\Modules\Track\EventHandlers\HandleNewTrackSlugIDGeneration;
use App\Modules\Track\EventHandlers\MediaMenus;
use App\Modules\Track\EventHandlers\TrackMenuMetaBox;
use App\Modules\Track\Events\OnArtistCreate;
use App\Modules\Track\Events\OnLicenseCreate;
use App\Modules\Track\Events\OnTrackCreate;
use App\Modules\Track\Events\OnTrackDefaultField;
use App\Modules\Track\Routes\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class TrackActivator implements ModuleConfig, PluginConfig
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
                HandleNewTrackSlugIDGeneration::class
            ],

            OnArtistCreate::class => [

            ],

            OnLicenseCreate::class => [

            ],

            OnMenuMetaBox::class => [
                GenreMenuMetaBox::class,
                TrackMenuMetaBox::class
            ],

            OnAdminMenu::class => [
                MediaMenus::class
            ],


            OnTrackDefaultField::class => [
                DefaultTrackFieldHandler::class
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
                Tables::getTable(Tables::ARTISTS) => Tables::getTable(Tables::ARTISTS),
                Tables::getTable(Tables::GENRES) => Tables::getTable(Tables::GENRES),
                Tables::getTable(Tables::LICENSES) => Tables::getTable(Tables::LICENSES),
                Tables::getTable(Tables::PURCHASES) => Tables::getTable(Tables::PURCHASES),
                Tables::getTable(Tables::PURCHASE_TRACKS) => Tables::getTable(Tables::PURCHASE_TRACKS),
                Tables::getTable(Tables::TRACKS) => Tables::getTable(Tables::TRACKS),
                Tables::getTable(Tables::TRACK_LIKES) => Tables::getTable(Tables::TRACK_LIKES),
                Tables::getTable(Tables::WISH_LIST) => Tables::getTable(Tables::WISH_LIST),
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
            "version" => '1-O-Ola.1654594213',
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
}