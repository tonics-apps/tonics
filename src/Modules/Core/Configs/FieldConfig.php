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

    public static function DefaultFieldItems(): array
    {
        $json =<<<'JSON'
[
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"596c407bfb4f4d17bff1b1840c7fcf\",\"field_input_name\":\"post_experience\",\"fieldName\":\"Post Experience\",\"inputName\":\"post_experience\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3a30ae4e2d2ebf3f3e6b45f35b3445\",\"field_input_name\":\"post_title\",\"fieldName\":\"Post Title\",\"inputName\":\"post_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Title Here\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"28d4f12fdd87c72c2217e68f781b7c\",\"field_input_name\":\"post_content\",\"fieldName\":\"Post Content\",\"inputName\":\"post_content\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"cd79f31a02ebaf382524255bb9977f\",\"field_input_name\":\"post_settings\",\"fieldName\":\"Post Settings\",\"inputName\":\"post_settings\",\"row\":\"2\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "media_media-image",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"c37249cd27fa2cf9fb227c48f80a96\",\"field_input_name\":\"image_url\",\"fieldName\":\"Featured Image\",\"inputName\":\"image_url\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "post_postcategoryselect",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postcategoryselect\",\"post_postcategoryselect_cell\":\"2\",\"field_slug_unique_hash\":\"98a6eec61986b338a606a293d52641\",\"field_input_name\":\"fk_cat_id\",\"fieldName\":\"Posts Category\",\"inputName\":\"fk_cat_id\",\"multipleSelect\":\"1\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "post_postauthorselect",
    "field_id": 7,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postauthorselect\",\"post_postauthorselect_cell\":\"3\",\"field_slug_unique_hash\":\"108w14fmdpow000000000\",\"field_input_name\":\"user_id\",\"fieldName\":\"Author\",\"inputName\":\"user_id\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"4\",\"field_slug_unique_hash\":\"63xrwrywapc0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 9,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"qnje9n94xxs000000000\",\"field_input_name\":\"post_slug\",\"fieldName\":\"Post Slug\",\"inputName\":\"post_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Post Slug (optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_select",
    "field_id": 10,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"48jzxkcjymw0000000000\",\"field_input_name\":\"post_status\",\"fieldName\":\"Status\",\"inputName\":\"post_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_date",
    "field_id": 11,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"13mgpl0rp37g000000000\",\"field_input_name\":\"created_at\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5dk0d0jv6uk0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Category Experience\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"lkrrixgthjk000000000\",\"field_input_name\":\"cat_name\",\"fieldName\":\"Category Title\",\"inputName\":\"cat_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Title Here\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"1pv65aei9cjk000000000\",\"field_input_name\":\"cat_content\",\"fieldName\":\"Category Content\",\"inputName\":\"cat_content\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5xxzokq5lo40000000000\",\"field_input_name\":\"\",\"fieldName\":\"Category Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "post_postcategoryselect",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postcategoryselect\",\"post_postcategoryselect_cell\":\"1\",\"field_slug_unique_hash\":\"30hrng80bsq0000000000\",\"field_input_name\":\"cat_parent_id\",\"fieldName\":\"Parent Category\",\"inputName\":\"cat_parent_id\",\"multipleSelect\":\"0\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"2yakqy9yrx00000000000\",\"field_input_name\":\"\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_text",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3mcl6jrdna00000000000\",\"field_input_name\":\"cat_slug\",\"fieldName\":\"Category Slug\",\"inputName\":\"cat_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Category Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_select",
    "field_id": 8,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4jv8e05l2co0000000000\",\"field_input_name\":\"cat_status\",\"fieldName\":\"Category Status\",\"inputName\":\"cat_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_date",
    "field_id": 9,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"4ffhzgcapki0000000000\",\"field_input_name\":\"created_at\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"field_input_name\":\"\",\"fieldName\":\"Track Experience\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2zs6itust540000000000\",\"field_input_name\":\"track_title\",\"fieldName\":\"Track Title\",\"inputName\":\"track_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Track Title Here\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"6gdz75f8uqw0000000000\",\"field_input_name\":\"track_content\",\"fieldName\":\"Track Content\",\"inputName\":\"track_content\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Track Settings\",\"inputName\":\"\",\"row\":\"3\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"74cf7kls1jk0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Featured Asset\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "media_media-image",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"5otpehs2q9o0000000000\",\"field_input_name\":\"image_url\",\"fieldName\":\"Featured Image\",\"inputName\":\"image_url\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "media_media-audio",
    "field_id": 7,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"media_media-audio\",\"media_media-audio_cell\":\"1\",\"field_slug_unique_hash\":\"5pa7nlu8thk0000000000\",\"field_input_name\":\"audio_url\",\"fieldName\":\"Featured Audio\",\"inputName\":\"audio_url\",\"featured_audio\":\"\",\"audio_url\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"2hlqfa9tbts0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 9,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3f6do5um7eu0000000000\",\"field_input_name\":\"track_slug\",\"fieldName\":\"Slug\",\"inputName\":\"track_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Track Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_select",
    "field_id": 10,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6zmwyh9ws7c0000000000\",\"field_input_name\":\"track_status\",\"fieldName\":\"Status\",\"inputName\":\"track_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_date",
    "field_id": 11,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"35geneeemj60000000000\",\"field_input_name\":\"created_at\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackgenreselect",
    "field_id": 12,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackgenreselect\",\"track_trackgenreselect_cell\":\"3\",\"field_slug_unique_hash\":\"yv63jfn7dxc000000000\",\"field_input_name\":\"fk_genre_id\",\"fieldName\":\"Genre\",\"inputName\":\"fk_genre_id\",\"multipleSelect\":\"1\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackcategoryselect",
    "field_id": 13,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackcategoryselect\",\"track_trackcategoryselect_cell\":\"3\",\"field_slug_unique_hash\":\"6tvcjh7h4qg0000000000\",\"field_input_name\":\"fk_track_cat_id\",\"fieldName\":\"Category\",\"inputName\":\"fk_track_cat_id\",\"multipleSelect\":\"1\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_tracklicenseselect",
    "field_id": 14,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_tracklicenseselect\",\"track_tracklicenseselect_cell\":\"3\",\"field_slug_unique_hash\":\"112o1lw7n05s000000000\",\"field_input_name\":\"fk_license_id\",\"fieldName\":\"License\",\"inputName\":\"fk_license_id\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackartistselect",
    "field_id": 15,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackartistselect\",\"track_trackartistselect_cell\":\"4\",\"field_slug_unique_hash\":\"4w48rjsg7de0000000000\",\"field_input_name\":\"fk_artist_id\",\"fieldName\":\"Artist\",\"inputName\":\"fk_artist_id\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 16,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"5\",\"field_slug_unique_hash\":\"5jicx4w4n8o0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Others\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_fieldselectiondropper",
    "field_id": 17,
    "field_parent_id": 16,
    "field_options": "{\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"1\",\"field_slug_unique_hash\":\"6gfokal3f040000000000\",\"field_input_name\":\"track_filter_meta\",\"fieldName\":\"Filter Meta\",\"inputName\":\"track_filter_meta\",\"fieldSlug\":[\"track-default-filter\"],\"defaultFieldSlug\":\"track-default-filter\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 18,
    "field_parent_id": 16,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"7epby7s7kgg000000000\",\"field_input_name\":\"track_markers_container\",\"fieldName\":\"Markers\",\"inputName\":\"track_markers_container\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumnrepeater",
    "field_id": 19,
    "field_parent_id": 18,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"5edpvzcchag0000000000\",\"field_input_name\":\"track_marker\",\"fieldName\":\"Track Marker\",\"inputName\":\"track_marker\",\"row\":\"1\",\"column\":\"4\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add More Track Marker\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 20,
    "field_parent_id": 19,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"42mq39hj2zu0000000000\",\"field_input_name\":\"track_marker_slug_id\",\"fieldName\":\"Slug ID (Optional)\",\"inputName\":\"track_marker_slug_id\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Unique Track Slug ID\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 21,
    "field_parent_id": 19,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"5tbithfcyss0000000000\",\"field_input_name\":\"track_marker_start\",\"fieldName\":\"Start Position\",\"inputName\":\"track_marker_start\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"i.e 1:35 or 00:1:35\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 22,
    "field_parent_id": 19,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"3\",\"field_slug_unique_hash\":\"3848wdbtvgo0000000000\",\"field_input_name\":\"track_marker_end\",\"fieldName\":\"End Position\",\"inputName\":\"track_marker_end\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"i.e 4:00 or 00:4:00\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 23,
    "field_parent_id": 19,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"4\",\"field_slug_unique_hash\":\"1ierzftuw5c0000000000\",\"field_input_name\":\"track_marker_name\",\"fieldName\":\"Marker Name\",\"inputName\":\"track_marker_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"i.e Chorus\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PageSlug\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"field_input_name\":\"page_experience\",\"fieldName\":\"Page Experience\",\"inputName\":\"page_experience\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PageSlug\"],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3f02xf18sko0000000000\",\"field_input_name\":\"page_slug\",\"fieldName\":\"Page Slug\",\"inputName\":\"page_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Page Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_select",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"k7mheu3xnto00000000\",\"field_input_name\":\"page_status\",\"fieldName\":\"Page Status\",\"inputName\":\"page_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0:Draft,1:Publish,-1:Trash\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1pyxksvf39b4000000000\",\"field_input_name\":\"page_title\",\"fieldName\":\"Page Title\",\"inputName\":\"page_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Page Title\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "page_pagetemplateselection",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"page_pagetemplateselection\",\"page_pagetemplateselection_cell\":\"1\",\"field_slug_unique_hash\":\"54cw77g52yc0000000000\",\"field_input_name\":\"page_template\",\"fieldName\":\"Page Template\",\"inputName\":\"page_template\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4yl3ix1a5280000000000\",\"field_input_name\":\"\",\"fieldName\":\"SEO Settings\",\"inputName\":\"\",\"row\":\"3\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"17ols4a28d5s000000000\",\"field_input_name\":\"seo_settings_basic\",\"fieldName\":\"Basic\",\"inputName\":\"seo_settings_basic\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"7glk8wuk0aw0000000000\",\"field_input_name\":\"seo_title\",\"fieldName\":\"Title (Optional)\",\"inputName\":\"seo_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"65\",\"placeholder\":\"Auto-generate from title if empty\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4w1vg0g41i40000000000\",\"field_input_name\":\"seo_description\",\"fieldName\":\"Description (Optional)\",\"inputName\":\"seo_description\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"250\",\"placeholder\":\"Enter SEO Description\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"14blceu1kl6k000000000\",\"field_input_name\":\"seo_settings_canonical_and_more\",\"fieldName\":\"Settings\",\"inputName\":\"seo_settings_canonical_and_more\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6ivu9jbpixc0000000000\",\"field_input_name\":\"seo_canonical_url\",\"fieldName\":\"Canonical URL\",\"inputName\":\"seo_canonical_url\",\"textType\":\"url\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Default to content URL\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_select",
    "field_id": 7,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4v28rctp7080000000000\",\"field_input_name\":\"seo_indexing\",\"fieldName\":\"Indexing\",\"inputName\":\"seo_indexing\",\"selectData\":\"1:index,0:noindex\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_select",
    "field_id": 8,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4g1g3x0zpe00000000000\",\"field_input_name\":\"seo_following\",\"fieldName\":\"Following\",\"inputName\":\"seo_following\",\"selectData\":\"1:follow,0:nofollow\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_select",
    "field_id": 9,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"3\",\"field_slug_unique_hash\":\"6fazxbd6gxs0000000000\",\"field_input_name\":\"seo_open_graph_type\",\"fieldName\":\"Open Graph Type\",\"inputName\":\"seo_open_graph_type\",\"selectData\":\"article:Article,website:Website\",\"defaultValue\":\"article\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 10,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"4\",\"field_slug_unique_hash\":\"2quvrericv00000000000\",\"field_input_name\":\"seo_old_urls\",\"fieldName\":\"Old URLs (One Per Line)\",\"inputName\":\"seo_old_urls\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"(One Per Line) (Canonical URL must be set for redirection to work)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "modular_fieldselectiondropper",
    "field_id": 11,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"5\",\"field_slug_unique_hash\":\"1mxkpikxb8ww000000000\",\"field_input_name\":\"seo_structured_data\",\"fieldName\":\"Structured Data\",\"inputName\":\"seo_structured_data\",\"fieldSlug\":[\"app-tonicsseo-structured-data-product-review\",\"app-tonicsseo-structured-data-article\",\"app-tonicsseo-structured-data-faq\"],\"defaultFieldSlug\":\"app-tonicsseo-structured-data-article\",\"hideInUserEditForm\":\"0\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "menu_menus",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"menu_menus\",\"field_slug_unique_hash\":\"i5ap1q0rukg000000000\",\"field_input_name\":\"site_header_menu\",\"fieldName\":\"site_header\",\"inputName\":\"site_header_menu\",\"menuSlug\":\"header-menu\",\"displayName\":\"1\"}"
  },
  {
    "fk_field_id": "Site Footer",
    "field_name": "menu_menus",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"menu_menus\",\"field_slug_unique_hash\":\"1zqxqpbnlkhs000000000\",\"field_input_name\":\"site_footer_menu\",\"fieldName\":\"site_footer\",\"inputName\":\"site_footer_menu\",\"menuSlug\":\"footer-menu\",\"displayName\":\"0\"}"
  },
  {
    "fk_field_id": "Sidebar Widget",
    "field_name": "widget_widgets",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"widget_widgets\",\"field_slug_unique_hash\":\"tqtc2pjcx4g000000000\",\"field_input_name\":\"sidebar_widget\",\"fieldName\":\"sidebar_widget\",\"inputName\":\"sidebar_widget\",\"widgetSlug\":\"sidebar-widget\"}"
  },
  {
    "fk_field_id": "Upload App Page",
    "field_name": "media_media-manager",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"media_media-manager\",\"field_slug_unique_hash\":\"6hekf1492880000000000\",\"fieldName\":\"Upload App\",\"inputName\":\"plugin_url\",\"featured_link\":\"\",\"file_url\":\"\"}"
  },
  {
    "fk_field_id": "Post Query Builder",
    "field_name": "modular_rowcolumnrepeater",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"4z1rbnzzwho0000000000\",\"field_input_name\":\"post_query_builder_field\",\"fieldName\":\"Post Query Builder\",\"inputName\":\"post_query_builder_field\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"1fr 2fr\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"1\",\"repeat_button_text\":\"Add Post Query Builder\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Query Builder",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"737sstzo4iw000000000\",\"field_input_name\":\"post_query_builder_perPost\",\"fieldName\":\"Number of Post\",\"inputName\":\"post_query_builder_perPost\",\"textType\":\"number\",\"defaultValue\":\"10\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Number of Post To Retrieve\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Post Query Builder",
    "field_name": "input_select",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"68ekrqwt2fc0000000000\",\"field_input_name\":\"post_query_builder_orderBy\",\"fieldName\":\"Order By\",\"inputName\":\"post_query_builder_orderBy\",\"selectData\":\"asc:Ascending, desc:Descending\",\"defaultValue\":\"desc\"}"
  },
  {
    "fk_field_id": "Post Query Builder",
    "field_name": "modular_rowcolumnrepeater",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"2\",\"field_slug_unique_hash\":\"4pvzswsjd9q0000000000\",\"field_input_name\":\"post_query_builder_CategoryIn\",\"fieldName\":\"Category In\",\"inputName\":\"post_query_builder_CategoryIn\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add Category In\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Query Builder",
    "field_name": "input_select",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"47bcdkcxrd6000000000\",\"field_input_name\":\"categoryOperator\",\"fieldName\":\"Operator\",\"inputName\":\"categoryOperator\",\"selectData\":\"IN,NOT IN\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Post Query Builder",
    "field_name": "post_postcategoryselect",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postcategoryselect\",\"post_postcategoryselect_cell\":\"1\",\"field_slug_unique_hash\":\"cemz102yrg0000000000\",\"field_input_name\":\"post_query_builder_Category\",\"fieldName\":\"Choose Category\",\"inputName\":\"post_query_builder_Category\",\"multipleSelect\":\"1\"}"
  },
  {
    "fk_field_id": "oEmbed",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3w0qwk0r6o00000000000\",\"field_input_name\":\"\",\"fieldName\":\"OEmbed\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"2fr 1fr\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "oEmbed",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2c1i7nzwzhhc000000000\",\"field_input_name\":\"OEmbed_url\",\"fieldName\":\"URL\",\"inputName\":\"OEmbed_url\",\"textType\":\"url\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter URL\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "oEmbed",
    "field_name": "input_select",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"jdlj4o5tyag000000000\",\"field_input_name\":\"OEmbed_responsive\",\"fieldName\":\"Responsive\",\"inputName\":\"OEmbed_responsive\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "oEmbed",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"33mogqqpskw0000000000\",\"field_input_name\":\"OEmbed_width\",\"fieldName\":\"Width (Optional\",\"inputName\":\"OEmbed_width\",\"textType\":\"number\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"OEmbed Width (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "oEmbed",
    "field_name": "input_text",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"4p1qor96y600000000000\",\"field_input_name\":\"OEmbed_height\",\"fieldName\":\"Height (Optional)\",\"inputName\":\"OEmbed_height\",\"textType\":\"number\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"OEmbed Height (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"59soo7mhdh80000000000\",\"field_input_name\":\"\",\"fieldName\":\"Track Category Experience\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5oh1qdx1u3g0000000000\",\"field_input_name\":\"track_cat_name\",\"fieldName\":\"Track Category Title\",\"inputName\":\"track_cat_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Title Here\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"217p13kr50cg000000000\",\"field_input_name\":\"track_cat_content\",\"fieldName\":\"Track Category Content\",\"inputName\":\"track_cat_content\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4xwgii68v0c0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Track Category Settings\",\"inputName\":\"\",\"row\":\"3\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "track_trackcategoryselect",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackcategoryselect\",\"track_trackcategoryselect_cell\":\"1\",\"field_slug_unique_hash\":\"62kefaada100000000000\",\"field_input_name\":\"track_cat_parent_id\",\"fieldName\":\"Parent Category\",\"inputName\":\"track_cat_parent_id\",\"multipleSelect\":\"0\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"6tlmxxp9fow0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "input_text",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1gow6mdy422o000000000\",\"field_input_name\":\"track_cat_slug\",\"fieldName\":\"Track Category Slug\",\"inputName\":\"track_cat_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Track Category Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "input_select",
    "field_id": 8,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"46mw023nprk0000000000\",\"field_input_name\":\"track_cat_status\",\"fieldName\":\"Track Category Status\",\"inputName\":\"track_cat_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "input_date",
    "field_id": 9,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"2jy8awqg57m0000000000\",\"field_input_name\":\"created_at\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Category Page",
    "field_name": "modular_fieldselectiondropper",
    "field_id": 10,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"3\",\"field_slug_unique_hash\":\"6f56nqh6mbc000000000\",\"field_input_name\":\"filter_type\",\"fieldName\":\"Filter Type\",\"inputName\":\"filter_type\",\"fieldSlug\":[\"track-default-filter\",\"track-default-filter-sample-packs\",\"track-default-filter-acapella\"],\"defaultFieldSlug\":\"track-default-filter\",\"hideInUserEditForm\":\"0\",\"expandField\":\"0\"}"
  },
  {
    "fk_field_id": "Track Default Filter",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3s8xh6pdk9o0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Filter\",\"inputName\":\"\",\"row\":\"3\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Default Filter",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6h2eyvj0ab00000000000\",\"field_input_name\":\"track_bpm\",\"fieldName\":\"BPM\",\"inputName\":\"track_bpm\",\"textType\":\"number\",\"defaultValue\":\"125\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Track BPM\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Default Filter",
    "field_name": "input_select",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"2\",\"field_slug_unique_hash\":\"3aq5iq8e950000000000\",\"field_input_name\":\"track_default_filter_keys\",\"fieldName\":\"Keys\",\"inputName\":\"track_default_filter_keys\",\"selectData\":\"A,Am,A#,A#m,B,Bm,C,Cm,C#,C#m,D,Dm,D#,D#m,E,Em,F,Fm, F#,F#m,G,Gm,G#,G#m\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Default Filter",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"3\",\"field_slug_unique_hash\":\"5317vtir3as0000000000\",\"field_input_name\":\"track_default_filter_duration\",\"fieldName\":\"Duration\",\"inputName\":\"track_default_filter_duration\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"I.e, 00:23 or 01:20 or 00:01:20\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Default Filter",
    "field_name": "input_select",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"4\",\"field_slug_unique_hash\":\"f1rrmva09uw000000000\",\"field_input_name\":\"track_default_filter_mood\",\"fieldName\":\"Mood\",\"inputName\":\"track_default_filter_mood\",\"selectData\":\"Atmospheric,Dark,Dreamy,Emotional,Energetic,Exotic,Funky,Happy,Hopeful,Hypnotic,Intense,Intimate,Melancholic,Mellow, Mysterious,Nostalgic,Passionate,Peaceful,Playful,Reflective, Relaxed,Raw,Sad,Sensual,Soulful,Triumphant,Uplifting\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Default Filter",
    "field_name": "input_choices",
    "field_id": 6,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"input_choices\",\"input_choices_cell\":\"5\",\"field_slug_unique_hash\":\"1juyd114ur28000000000\",\"field_input_name\":\"track_default_filter_instruments\",\"fieldName\":\"Instruments\",\"inputName\":\"track_default_filter_instruments\",\"choiceType\":\"checkbox\",\"choices\":\"Accordion:Accordion,Acoustic Bass:Acoustic Bass,Acoustic Grand Piano:Acoustic Grand Piano,Acoustic Guitar (nylon):Acoustic Guitar (nylon),Acoustic Guitar (steel):Acoustic Guitar (steel),Agogo:Agogo,Alto Sax:Alto Sax,Applause:Applause,Bagpipe:Bagpipe,Banjo:Banjo,Baritone Sax:Baritone Sax,Bass:Bass,Bassoon:Bassoon,Bird Tweet:Bird Tweet,Blown Bottle:Blown Bottle,Bongos:Bongos,Brass Section:Brass Section,Breath Noise:Breath Noise,Bright Acoustic Piano:Bright Acoustic Piano,Celesta:Celesta,Cello:Cello,Choir Aahs:Choir Aahs,Church Organ:Church Organ,Clarinet:Clarinet,Clavinet:Clavinet,Congas:Congas,Contrabass:Contrabass,Cymbals:Cymbals,Distortion Guitar:Distortion Guitar,Drawbar Organ:Drawbar Organ,Drums:Drums,Dulcimer:Dulcimer,Electric Bass (finger):Electric Bass (finger),Electric Bass (pick):Electric Bass (pick),Electric Grand Piano:Electric Grand Piano,Electric Guitar (clean):Electric Guitar (clean),Electric Guitar (jazz):Electric Guitar (jazz),Electric Guitar (muted):Electric Guitar (muted),Electric Piano 1:Electric Piano 1,Electric Piano 2:Electric Piano 2,English Horn:English Horn,FX 1 (rain):FX 1 (rain),FX 2 (soundtrack):FX 2 (soundtrack),FX 3 (crystal):FX 3 (crystal),FX 4 (atmosphere):FX 4 (atmosphere),FX 5 (brightness):FX 5 (brightness),FX 6 (goblins):FX 6 (goblins),FX 7 (echoes):FX 7 (echoes),FX 8 (sci-fi):FX 8 (sci-fi),Fiddle:Fiddle,Flute:Flute,French Horn:French Horn,Fretless Bass:Fretless Bass,Glockenspiel:Glockenspiel,Guitar Fret Noise:Guitar Fret Noise,Guitar Harmonics:Guitar Harmonics,Guitar:Guitar,Gunshot:Gunshot,Harmonica:Harmonica,Harp:Harp,Harpsichord:Harpsichord,Helicopter:Helicopter,Honky-tonk Piano:Honky-tonk Piano,Kalimba:Kalimba,Koto:Koto,Lead 1 (square):Lead 1 (square),Lead 2 (sawtooth):Lead 2 (sawtooth),Lead 3 (calliope):Lead 3 (calliope),Lead 4 (chiff):Lead 4 (chiff),Lead 5 (charang):Lead 5 (charang),Lead 6(voice):Lead 6 (voice),Lead 7 (fifths):Lead 7 (fifths),Lead 8 (bass + lead):Lead 8 (bass + lead),Mandolin:Mandolin,Maracas:Maracas,Marimba:Marimba,Melodic Tom:Melodic Tom,Music Box:Music Box,Muted Trumpet:Muted Trumpet,Oboe:Oboe,Ocarina:Ocarina,Orchestra Hit:Orchestra Hit,Orchestral Harp:Orchestral Harp,Overdriven Guitar:Overdriven Guitar,Pad 1 (new age):Pad 1 (new age),Pad 2 (warm):Pad 2 (warm),Pad 3 (polysynth):Pad 3 (polysynth),Pad 4 (choir):Pad 4 (choir),Pad 5 (bowed):Pad 5 (bowed),Pad 6 (metallic):Pad 6 (metallic),Pad 7 (halo):Pad 7 (halo),Pad 8 (sweep):Pad 8 (sweep),Pan Flute:Pan Flute,Percussive Organ:Percussive Organ,Piano:Piano,Piccolo:Piccolo,Pizzicato Strings:Pizzicato Strings,Recorder:Recorder,Reed Organ:Reed Organ,Reverse Cymbal:Reverse Cymbal,Rock Organ:Rock Organ,Saxophone:Saxophone,Seashore:Seashore,Shakuhachi:Shakuhachi,Shamisen:Shamisen,Shanai:Shanai,Sitar:Sitar,Slap Bass 1:Slap Bass 1,Slap Bass 2:Slap Bass 2,Snare drum:Snare drum,Soprano Sax:Soprano Sax,Steel Drums:Steel Drums,Steel drums:Steel drums,String Ensemble 1:String Ensemble 1,String Ensemble 2:String Ensemble 2,Synth Bass 1:Synth Bass 1,Synth Bass 2:Synth Bass 2,Synth Brass 1:Synth Brass 1,Synth Brass 2:Synth Brass 2,Synth Drum:Synth Drum,Synth Strings 1:Synth Strings 1,Synth Strings 2:Synth Strings 2,Synth Voice:Synth Voice,Tabla:Tabla,Taiko Drum:Taiko Drum,Tango Accordion:Tango Accordion,Telephone Ring:Telephone Ring,Tenor Sax:Tenor Sax,Timpani:Timpani,Tinkle Bell:Tinkle Bell,Tremolo Strings:Tremolo Strings,Triangle:Triangle,Trombone:Trombone,Trumpet:Trumpet,Tuba:Tuba,Tubular Bells:Tubular Bells,Vibraphone:Vibraphone,Viola:Viola,Violin:Violin,Vocals:Vocals,Voice Oohs:Voice Oohs,Whistle:Whistle,Woodblock:Woodblock,Xylophone:Xylophone\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Default Filter",
    "field_name": "modular_fieldselectiondropper",
    "field_id": 7,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"modular_fieldselectiondropper\",\"modular_fieldselectiondropper_cell\":\"6\",\"field_slug_unique_hash\":\"4781z2j9hc80000000000\",\"field_input_name\":\"track_default_filter_moreFilters\",\"fieldName\":\"More Filters\",\"inputName\":\"track_default_filter_moreFilters\",\"fieldSlug\":[\"track-default-filter-sample-packs\",\"track-default-filter-acapella\"],\"defaultFieldSlug\":\"track-default-filter-sample-packs\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\"}"
  },
  {
    "fk_field_id": "Track Default Filter »» [Sample Packs]",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"eqttwypyztk000000000\",\"field_input_name\":\"track_default_filter_samplePacks\",\"fieldName\":\"Sample Packs Filter\",\"inputName\":\"track_default_filter_samplePacks\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Default Filter »» [Sample Packs]",
    "field_name": "input_choices",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"76hyys33dk80000000000\",\"field_input_name\":\"track_default_filter_samplePacks_Type\",\"fieldName\":\"Sample Packs Type\",\"inputName\":\"track_default_filter_samplePacks_Type\",\"choiceType\":\"checkbox\",\"choices\":\"Bass:Bass,Construction kit:Construction kit,Drum:Drum,FX:FX,Full track:Full track,Guitar:Guitar,Live instrument:Live instrument,Loop:Loop,Midi:Midi,One-shots:One-shots,Piano:Piano,Percussion:Percussion,Preset:Preset,Sample:Sample,Synth:Synth,Vocal:Vocal\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Default Filter »» [Acapella]",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6jtq847s9hc0000000000\",\"field_input_name\":\"track_default_filter_acapella\",\"fieldName\":\"Acapella Filter\",\"inputName\":\"track_default_filter_acapella\",\"row\":\"3\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Default Filter »» [Acapella]",
    "field_name": "input_select",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3b7ekb66h7s0000000000\",\"field_input_name\":\"track_default_filter_acapella_gender\",\"fieldName\":\"Gender\",\"inputName\":\"track_default_filter_acapella_gender\",\"selectData\":\"Male,Female,Male & Female\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Default Filter »» [Acapella]",
    "field_name": "input_choices",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"input_choices\",\"input_choices_cell\":\"2\",\"field_slug_unique_hash\":\"4o0baxfiq2g0000000000\",\"field_input_name\":\"track_default_filter_acapella_vocalStyle\",\"fieldName\":\"Vocal Style\",\"inputName\":\"track_default_filter_acapella_vocalStyle\",\"choiceType\":\"checkbox\",\"choices\":\"Accapella:Accapella,Adlib:Adlib, Harmonies:Harmonies,Melody:Melody,Rap:Rap,Spoken word:Spoken word,Vocal chop:Vocal chop,Vocal effect:Vocal effect,Vocal harmony:Vocal harmony,Vocal loop:Vocal loop,Vocal one-shot:Vocal one-shot,Vocal sample:Vocal sample,Vocal sound effect:Vocal sound effect,Whispering:Whispering\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Default Filter »» [Acapella]",
    "field_name": "input_select",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"3\",\"field_slug_unique_hash\":\"88f2lv5iw2g000000000\",\"field_input_name\":\"track_default_filter_acapella_emotion\",\"fieldName\":\"Emotion\",\"inputName\":\"track_default_filter_acapella_emotion\",\"selectData\":\"Angry:Angry,Sad:Sad,Happy:Happy,Emotional:Emotional,Passionate:Passionate,Soulful:Soulful,Intense:Intense,Playful:Playful,Melancholic:Melancholic,Nostalgic:Nostalgic,Hypnotic:Hypnotic,Mysterious:Mysterious,Mellow:Mellow,Relaxed:Relaxed,Reflective:Reflective\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Default Filter »» [Acapella]",
    "field_name": "input_select",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"4\",\"field_slug_unique_hash\":\"2r4hsnobk860000000000\",\"field_input_name\":\"track_default_filter_acapella_scale\",\"fieldName\":\"Scale\",\"inputName\":\"track_default_filter_acapella_scale\",\"selectData\":\"Alto:Alto,Baritone:Baritone,Bass:Bass,Countertenor:Countertenor,Mezzo-soprano:Mezzo-soprano,Soprano:Soprano,Tenor:Tenor\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Default Filter »» [Acapella]",
    "field_name": "input_choices",
    "field_id": 6,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"input_choices\",\"input_choices_cell\":\"5\",\"field_slug_unique_hash\":\"40tgb29l7ru0000000000\",\"field_input_name\":\"track_default_filter_acapella_effects\",\"fieldName\":\"Effects\",\"inputName\":\"track_default_filter_acapella_effects\",\"choiceType\":\"checkbox\",\"choices\":\"Autotune:Autotune,Chorus:Chorus,Delay:Delay,Echo:Echo,Flanger:Flanger,Harmony:Harmony,Phaser:Phaser,Reverb:Reverb,Vibrato:Vibrato,Distortion:Distortion,Pitch shift:Pitch shift,Compression:Compression,EQ:EQ,Filtering:Filtering,Volume:Volume,Wah-wah:Wah-wah\",\"defaultValue\":\"\"}"
  }
]
JSON;
        return json_decode($json);
    }

}