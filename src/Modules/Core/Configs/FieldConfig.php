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

use App\InitLoaderMinimal;

class FieldConfig
{
    public static function fieldPreSavedDataID(): string
    {
        return '987654321123456789_preSavedFieldID_should_never_be_used_as_a_field_slug';
    }

    /**
     * @throws \Exception
     */
    public static function hasPreSavedFieldData(): bool
    {
        return isset(getPostData()[self::fieldPreSavedDataID()]);
    }

    /**
     * @throws \Exception
     */
    public static function getPreSavedFieldData()
    {
        return getPostData()[self::fieldPreSavedDataID()];
    }

    public static function fieldSettingsID(): string
    {
        return '987654321123456789_fieldSettingsID_should_never_be_used_as_a_field_slug';
    }

    /**
     * @throws \Exception
     */
    public static function hasFieldSettings(): bool
    {
        return InitLoaderMinimal::globalVariableKeyExist(self::fieldSettingsID());
    }

    /**
     * @throws \Exception
     */
    public static function getFieldSettings()
    {
        return AppConfig::initLoaderMinimal()::getGlobalVariableData(self::fieldSettingsID()) ?? [];
    }

    public static function postEditorFieldsContentID(): string
    {
        return '987654321123456789_postEditorFieldsContentID_should_never_be_used_as_a_field_slug';
    }

    /**
     * @throws \Exception
     */
    public static function hasPostEditorFieldContent(): bool
    {
        return InitLoaderMinimal::globalVariableKeyExist(self::postEditorFieldsContentID());
    }

    /**
     * @throws \Exception
     */
    public static function getPostEditorFieldsContent()
    {
        return AppConfig::initLoaderMinimal()::getGlobalVariableData(self::postEditorFieldsContentID()) ?? [];
    }

    /**
     * @param $settingFile
     * Only File That Contains JSON data
     * @param $data
     * @return false
     * @throws \Exception
     */
    public static function savePluginFieldSettings($settingFile, array $data): bool
    {
        if (str_starts_with($settingFile, AppConfig::getAppsPath()) && helper()->isReadable($settingFile) && helper()->isWritable($settingFile)){
            $settings = @file_get_contents($settingFile);
            if (!$settings){
                return false;
            }
            if (!helper()->isJSON($settings)){
                return false;
            }
            $settings = json_decode($settings, true);
            $settings = helper()->mergeKeyIntersection($settings, $data);
            return @file_put_contents($settingFile, json_encode($settings));
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public static function loadPluginSettings($settingFile): array
    {
        if (str_starts_with($settingFile, AppConfig::getAppsPath()) && helper()->isReadable($settingFile)){
            $settings = @file_get_contents($settingFile);
            if (!$settings){
                return [];
            }
            if (!helper()->isJSON($settings)){
                return [];
            }
            return json_decode($settings, true) ?? [];
        }

        return [];
    }


}