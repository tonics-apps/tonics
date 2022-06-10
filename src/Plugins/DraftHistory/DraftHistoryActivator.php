<?php

namespace App\Plugins\DraftHistory;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Post\EventHandlers\PostMenus;
use Devsrealm\TonicsRouterSystem\Route;

class DraftHistoryActivator implements ModuleConfig, PluginConfig
{

    /**
     * @return bool
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function events(): array
    {
        return [
            OnAdminMenu::class => [
               // PostMenus::class
            ],
        ];
    }

    /**
     * @param Route $routes
     * @return Route
     */
    public function route(Route $routes): Route
    {
        return $routes;
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return [];
    }

    /**
     *
     */
    public function onInstall(): void
    {
        // TODO: Implement onInstall() method.
    }

    /**
     *
     */
    public function onUninstall(): void
    {
        // TODO: Implement onUninstall() method.
    }

    /**
     * @return array
     */
    public function info(): array
    {
        return [
            "name" => "DraftHistory",
            "type" => "Plugin",
            "version" => '1-O-Ola.1654594213',
            "description" => "DraftHistory Plugin, The First Tonic Plugin, Basically Useless For Now",
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