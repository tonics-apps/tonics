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