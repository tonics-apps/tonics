<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Configs;


class DriveConfig
{
    /*
     * @return string
     */
    public static function getPrivatePath(): string
    {
        return dirname(APP_ROOT) . '/private';
    }

    /**
     * @return string
     */
    public static function getUploadsPath(): string
    {
        return dirname(APP_ROOT) . '/private/uploads';
    }

    /**
     * This is where you dump temp data, never dump temp data in the uploads path
     * @return string
     */
    public static function getTempPath(): string
    {
        return dirname(APP_ROOT) . '/private/temp';
    }

    public static function getTempPathForModules(): string
    {
        return self::getTempPath() . '/modules';
    }

    public static function getTempPathForPlugins(): string
    {
        return  self::getTempPath() . '/plugins';
    }

    public static function getTempPathForThemes(): string
    {
        return  self::getTempPath() . '/themes';
    }

    public static function getTempPathForFull(): string
    {
        return  self::getTempPath() . '/full';
    }
    /**
     * @return string
     */
    public static function getWordPressImportPath(): string
    {
        return dirname(APP_ROOT) . '/private/uploads/wordpress_import';
    }

    /**
     * @return string
     */
    public static function getWordPressImportUploadsPath(): string
    {
        return dirname(APP_ROOT) . '/private/uploads/wordpress_import/uploads';
    }

    public static function getDropBoxKey(): string
    {
        return env('DROPBOX_KEY', '');
    }
}