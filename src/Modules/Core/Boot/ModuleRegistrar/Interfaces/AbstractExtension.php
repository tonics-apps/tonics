<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

use App\Apps\TonicsAppStore\Route\Routes;
use App\Modules\Core\Configs\DatabaseConfig;
use Devsrealm\TonicsRouterSystem\Route;

abstract class AbstractExtension implements ExtensionConfig, FieldItemsExtensionConfig
{
    use Routes;

    static array $TABLES = [];

    public function enabled (): bool
    {
        return true;
    }

    public function events (): array
    {
        return [

        ];
    }

    public function route (Route $routes): Route
    {
        $route = $this->routeApi($routes);
        return $this->routeWeb($route);
    }

    public function tables (): array
    {
        return array_reduce(array_keys(self::$TABLES), function ($keys, $key) {
            $keys[self::getTable($key)] = self::$TABLES[$key];
            return $keys;
        }, []);
    }

    public function onInstall (): void {}

    public function onUninstall (): void {}

    public function onUpdate (): void {}

    public function onDelete (): void {}

    public function info (): array
    {
        return [
            "name"                 => "REPLACE_TO_APP_NAME",
            "type"                 => "App", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => 'REPLACE_WITH_VERSION_NUMBER.TIMESTAMP',
            "description"          => "REPLACE_WITH_APP_DESCRIPTION",
            "info_url"             => '',
            "settings_page"        => null, // can be null or a route name
            "update_discovery_url" => "",
            "authors"              => [
                "name"  => "Your Name",
                "email" => "name@website.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }

    public function fieldItems (): array|string
    {
        return [];
    }

    public static function getTable (string $tableName): string
    {
        if (!key_exists($tableName, self::$TABLES)) {
            throw new \InvalidArgumentException("`$tableName` is an invalid table name");
        }

        return DatabaseConfig::getPrefix() . $tableName;
    }
}