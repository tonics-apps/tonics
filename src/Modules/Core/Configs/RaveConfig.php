<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Configs;


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