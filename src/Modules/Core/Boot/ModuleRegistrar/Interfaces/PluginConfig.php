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

interface PluginConfig
{
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
     * Info can contain several array key and values.
     * @return array
     */
    public function info(): array;
}