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


use PDO;

class DatabaseConfig
{
    /**
     * @param string $driver
     * set the $driver, it defaults to mysql if nothing is set
     * @return string
     */
    public static function getDriver(string $driver = 'mysql'):string{
        return $driver;
    }

    public static function getUrl(string $dbUrl = null): ?string
    {
        return $dbUrl;
    }

    /**
     * @param string $port
     * Default to '3306' if no $port is found
     * @return string
     */
    public static function getPort(string $port = '3306'): string
    {
        return $port;
    }

    /**
     * getHost() is retrieved from the env file, you can't set this
     * @return string
     */
    public static function getHost(): string
    {
        return env('DB_HOST', '127.0.0.1');
    }


    /**
     * getDatabase() is retrieved from the env file, you can't set this
     * @return string
     */
    public static function getDatabase(): string
    {
        return env('DB_DATABASE', 'null');
    }

    /**
     * getUsername() is retrieved from the env file, you can't set this
     * @return string
     */
    public static function getUsername(): string
    {
        return env('DB_USERNAME', 'null');
    }

    /**
     * getPassword() is retrieved from the env file, you can't set this
     * @return string
     */
    public static function getPassword(): string
    {
        return env('DB_PASSWORD', 'null');
    }

    /**
     * getUnixSocket() is retrieved from the env file, you can't set this
     * @return string
     */
    public static function getUnixSocket(): string
    {
        return env('DB_SOCKET', '');
    }

    /**
     * getCharset() is retrieved from the env file, you can't set this
     * @return string
     */
    public static function getCharset(): string
    {
        return env('DB_CHARSET', 'utf8mb4');
    }

    /**
     * @param string $collation
     * Default to 'utf8mb4_unicode_ci if no $collation is set
     * @return string
     */
    public static function getCollation(string $collation = 'utf8mb4_unicode_ci'): string
    {
        return $collation;
    }

    /**
     * getPrefix() is retrieved from the env file, you can't set this
     * @return string
     */
    public static function getPrefix(): string
    {
        return env('DB_PREFIX', 'bt_');
    }

    /**
     * @return bool
     */
    public static function getPrefixIndexes(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function getStatic(): bool
    {
        return true;
    }

    /**
     * getEngine() is retrieved from the env file, you can't set this
     * @return string
     */
    public static function getEngine(): string
    {
        return env('DB_ENGINE', 'InnoDB');
    }

    /**
     * @return array
     */
    public static function getOptions(): array
    {
        return extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [];
    }


}