<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Configs;


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