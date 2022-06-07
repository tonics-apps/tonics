<?php

namespace App\Themes\NinetySeven;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Themes\NinetySeven\Route\Routes;
use Devsrealm\TonicsRouterSystem\Route;

class NinetySevenActivator implements ModuleConfig, PluginConfig
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
        return [];
    }

    /**
     * @inheritDoc
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        return $this->routeWeb($routes);
    }

    /**
     * @inheritDoc
     */
    public function tables(): array
    {
        return [];
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
            'type'
        ];
    }
}