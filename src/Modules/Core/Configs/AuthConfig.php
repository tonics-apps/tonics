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