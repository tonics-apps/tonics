<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Comment;
use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsRouterSystem\Route;

class CommentActivator implements ExtensionConfig
{

    public function enabled(): bool
    {
        return true;
    }

    public function events(): array
    {
        return [];
    }

    public function route(Route $routes): Route
    {
        return $routes;
    }

    public function tables(): array
    {
        return [
            Tables::getTable(Tables::COMMENT_USER_TYPE) => Tables::$TABLES[Tables::COMMENT_USER_TYPE],
            Tables::getTable(Tables::COMMENTS) => Tables::$TABLES[Tables::COMMENTS],
        ];
    }

    public function onInstall(): void
    {
        return;
    }

    public function onUninstall(): void
    {
        return;
    }

    public function onUpdate(): void
    {
        return;
    }

    public function onDelete(): void
    {
        return;
    }

    public function info(): array
    {
        return [
            "name" => "Comment",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1654594213',
            "stable" => 0,
            "description" => "The Comment Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-comment-module/releases/latest",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }
}