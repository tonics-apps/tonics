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
            "name" => "Premium Widgets",
            "slug" => "premium-widget",
            "version" => '1.0.0',
            "description" => "Contains More Widgets",
            "homepage" => "https://github.com/devsrealm/premium-widget",
            "featured_image" => "https://picsum.photos/350", // Recommended 350px by 350px
            "keywords" => [
                "devsrealm",
                "beatstonic",
                "draft-history"
            ],
            "license" => "MIT",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
        ];
    }
}