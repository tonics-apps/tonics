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


class DriveConfig
{
    /*
     * @return string
     */
    public static function getPrivatePath(): string
    {
        return dirname(APP_ROOT) . '/private';
    }

    public static function xAccelDownloadFilePath(): string
    {
        return "/download_file_path_987654321/";
    }

    public static function serveFilePath(): string
    {
        return "/serve_file_path_987654321/";
    }

    public static function xAccelAppFilePath(): string
    {
        return "/apps_file_path_987654321/";
    }

    public static function serveAppFilePath(): string
    {
        return "/serve_app_file_path_987654321/";
    }

    public static function xAccelModuleFilePath(): string
    {
        return "/modules_file_path_987654321/";
    }

    public static function serveModuleFilePath(): string
    {
        return "/serve_module_file_path_987654321/";
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

    public static function getTempPathForApps(): string
    {
        return  self::getTempPath() . '/apps';
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