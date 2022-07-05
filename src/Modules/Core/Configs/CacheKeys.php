<?php

namespace App\Modules\Core\Configs;

class CacheKeys
{
    public static function getSinglePostTemplateKey(): string
    {
        return env('APP_NAME', 'Tonics') . 'Single_Post';
    }
}