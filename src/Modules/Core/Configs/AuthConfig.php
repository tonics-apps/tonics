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

use App\Modules\Core\RequestInterceptor\Authenticated;
use App\Modules\Core\RequestInterceptor\CSRFGuard;
use App\Modules\Core\RequestInterceptor\StartSession;

class AuthConfig
{
    /**
     * This gets You the `StartSession` and `CSRFGuard` Interceptor
     * @param array $more
     * @return array
     */
    public static function getCSRFRequestInterceptor(array $more = []): array
    {
        $default = [StartSession::class, CSRFGuard::class];
        return [...$default, ...$more];
    }

    /**
     * This gets all the interceptors in `AuthConfig::getCSRFRequestInterceptor() `and `Authenticated` interceptor,
     * if you only want `Authenticated` interceptor, please use it as is.
     * @param array $more
     * @return array
     */
    public static function getAuthRequestInterceptor(array $more = []): array
    {
        return self::getCSRFRequestInterceptor([Authenticated::class, ...$more]);
    }
}