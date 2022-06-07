<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Configs;


class RaveConfig
{
    public static function getPublicKey(): string
    {
        return  env('RAVE_PUBLIC_KEY', 'null');
    }

    public static function getSecretKey(): string
    {
        return env('RAVE_SECRET_KEY', 'null');
    }

    public static function getStoreName(): string
    {
        return env('title', "Rave Payment Gateway");
    }

    public static function getEnv(): string
    {
        return env('RAVE_ENVIRONMENT', "staging");
    }

    public static function getLogo(): string
    {
        return env('RAVE_LOGO', "https://cdn.devsrealm.com/wp-content/uploads/2020/01/Devsreal-Author-Logo.svg");
    }

    public static function getPrefix(): string
    {
        return env('RAVE_PREFIX', "rave");
    }

    public static function getSecretHash(): string
    {
        return env('RAVE_SECRET_HASH', 'null');
    }

}