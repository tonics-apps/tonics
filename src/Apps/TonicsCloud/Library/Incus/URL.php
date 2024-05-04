<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\Library\Incus;

class URL
{
    // Request methods.
    public const REQUEST_GET    = 'GET';
    public const REQUEST_POST   = 'POST';
    public const REQUEST_HEAD   = 'HEAD';
    public const REQUEST_PATCH   = 'PATCH';
    public const REQUEST_PUT    = 'PUT';
    public const REQUEST_DELETE = 'DELETE';

    public const API_VERSION = '1.0';
    private static string $baseURL = '';

    public function __construct($baseURL = '') {
        self::$baseURL = $baseURL . "/" . self::API_VERSION;
    }

    const SERVER_EVENTS = '/events';
    const SERVER_RESOURCES = '/resources';

    const SERVER_ENV = '?public';

    /**
     * @param string $type
     * @return string
     */
    public static function SERVER_URL(string $type = ''): string
    {
        return self::$baseURL . $type;
    }


    const CERTIFICATE_ROOT = '/certificates';
    const CERTIFICATE_ADD = '/certificates?public';
    const CERTIFICATE_RECURSION = '/certificates?recursion=1';
    /**
     * @param string $type
     * @return string
     */
    public static function CERTIFICATE_URL(string $type = self::CERTIFICATE_ROOT): string
    {
        return self::$baseURL . $type;
    }

    const IMAGE_ROOT = '/images';
    const IMAGE_ALIASES = '/images/aliases';
    const IMAGE_PUBLIC = '/images?public';
    const IMAGE_PUBLIC_RECURSION = '/images?public&recursion=1';
    const IMAGE_RECURSION = '/images?recursion=1';
    public static function IMAGE_URL(string $type = self::IMAGE_ROOT): string
    {
        return self::$baseURL . $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function INSTANCE_URL(string $type = '/instances'): string
    {
        return self::$baseURL . $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function METRIC_URL(string $type = '/metrics'): string
    {
        return self::$baseURL . $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function OPERATION_URL(string $type = '/operations'): string
    {
        return self::$baseURL . $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function PROFILE_URL(string $type = '/profiles'): string
    {
        return self::$baseURL . $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function PROJECT_URL(string $type = '/projects'): string
    {
        return self::$baseURL . $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function STORAGE_URL(string $type = '/storage-pools'): string
    {
        return self::$baseURL . $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function WARNING_URL(string $type = '/warnings'): string
    {
        return self::$baseURL . $type;
    }

    /**
     * @return string
     */
    public static function getBaseURL(): string
    {
        return self::$baseURL;
    }

    /**
     * @param string $baseURL
     */
    public static function setBaseURL(string $baseURL): void
    {
        self::$baseURL = $baseURL;
    }

}