<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Library\ModuleRegistrar\Interfaces;


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