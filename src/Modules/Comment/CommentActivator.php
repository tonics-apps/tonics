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

namespace App\Modules\Comment;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsRouterSystem\Route;

class CommentActivator implements ExtensionConfig
{

    public function enabled (): bool
    {
        return true;
    }

    public function events (): array
    {
        return [];
    }

    public function route (Route $routes): Route
    {
        return $routes;
    }

    public function tables (): array
    {
        return [
            Tables::getTable(Tables::COMMENT_USER_TYPE) => Tables::$TABLES[Tables::COMMENT_USER_TYPE],
            Tables::getTable(Tables::COMMENTS)          => Tables::$TABLES[Tables::COMMENTS],
        ];
    }

    public function onInstall (): void
    {
        return;
    }

    public function onUninstall (): void
    {
        return;
    }

    public function onUpdate (): void
    {
        return;
    }

    public function onDelete (): void
    {
        return;
    }

    public function info (): array
    {
        return [
            "name"                 => "Comment",
            "type"                 => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-Ola.1718095500',
            "stable"               => 0,
            "description"          => "The Comment Module",
            "info_url"             => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-comment-module/releases/latest",
            "authors"              => [
                "name"  => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }
}