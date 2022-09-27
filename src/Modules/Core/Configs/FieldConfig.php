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

use App\Modules\Core\Boot\InitLoaderMinimal;
use App\Modules\Core\Library\Tables;

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

    public static function fieldUnSortedItemsDataID(): string
    {
        return '987654321123456789_fieldUnSortedItemsDataID_should_never_be_used_as_a_field_slug';
    }

    /**
     * @throws \Exception
     */
    public static function hasFieldUnSortedItemsDataID(): bool
    {
        return InitLoaderMinimal::globalVariableKeyExist(self::fieldUnSortedItemsDataID());
    }

    /**
     * @throws \Exception
     */
    public static function getFieldUnSortedItemsDataID()
    {
        return AppConfig::initLoaderMinimal()::getGlobalVariableData(self::fieldUnSortedItemsDataID()) ?? [];
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
     * @param $key
     * Only File That Contains JSON data
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public static function savePluginFieldSettings($key, array $data): array
    {
        $key = 'App_Settings_' . $key;
        if (isset($data['token'])){
            unset($data['token']);
        }

        $globalTable = Tables::getTable(Tables::GLOBAL);
        db(true)->insertOnDuplicate(
            $globalTable,
            [
                'key' => $key,
                'value' => json_encode($data)
            ],
            ['value']
        );

        apcu_clear_cache();
        return $data;
    }

    /**
     * @throws \Exception
     */
    public static function loadPluginSettings($key): array
    {
        if (!str_starts_with($key, 'App_Settings_')){
            $key = 'App_Settings_' . $key;
        }
        $globalTable = Tables::getTable(Tables::GLOBAL);
        $updates = db(true)->row("SELECT * FROM $globalTable WHERE `key` = ?", $key);
        if (isset($updates->value) && !empty($updates->value)){
            return json_decode($updates->value, true);
        }
        return [];
    }


}