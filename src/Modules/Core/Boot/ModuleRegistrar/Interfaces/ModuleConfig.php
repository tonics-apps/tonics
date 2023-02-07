<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Boot\ModuleRegistrar\Interfaces;


use Devsrealm\TonicsRouterSystem\Route;

interface ModuleConfig
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
}