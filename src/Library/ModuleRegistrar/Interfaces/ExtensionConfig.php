<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Library\ModuleRegistrar\Interfaces;

use Devsrealm\TonicsRouterSystem\Route;

interface ExtensionConfig
{
    /**
     * TO Determine if the Module or Plugin is Enabled
     * @return bool
     */
    public function enabled(): bool;

    /**
     * Array of Events and Handlers
     * @return array
     */
    public function events(): array;

    /**
     * @param Route $routes
     * @return Route
     */
    public function route(Route $routes): Route;

    /**
     * Tables in this module
     * @return array
     */
    public function tables(): array;

    /**
     * Would be called anytime an extension is installed
     * so, you can do your check here, e.g. update schema, or whatever
     * @return void
     */
    public function onInstall(): void;

    /**
     * Would be called anytime an extension is uninstalled
     * @return void
     */
    public function onUninstall(): void;

    /**
     * Would be called anytime an extension is updated,
     * so, you can do your check here, e.g. update schema, or whatever
     * @return void
     */
    public function onUpdate(): void;

    /**
     * Would be called anytime an extension is deleted,
     * @return void
     */
    public function onDelete(): void;

    /**
     * Info can contain several array key and values.
     * @return array
     */
    public function info(): array;
}