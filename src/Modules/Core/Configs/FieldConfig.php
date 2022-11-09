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
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"6gdz75f8uqw0000000000\",\"field_input_name\":\"track_content\",\"fieldName\":\"Track Content\",\"inputName\":\"track_content\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Track Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
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
    "field_name": "input_date",
    "field_id": 10,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"35geneeemj60000000000\",\"field_input_name\":\"created_at\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_select",
    "field_id": 11,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6zmwyh9ws7c0000000000\",\"field_input_name\":\"track_status\",\"fieldName\":\"Status\",\"inputName\":\"track_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackgenreradio",
    "field_id": 12,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackgenreradio\",\"track_trackgenreradio_cell\":\"3\",\"field_slug_unique_hash\":\"2n0vd2xqjxq0000000000\",\"field_input_name\":\"fk_genre_id\",\"fieldName\":\"Genre\",\"inputName\":\"fk_genre_id\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_tracklicenseselect",
    "field_id": 13,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_tracklicenseselect\",\"track_tracklicenseselect_cell\":\"3\",\"field_slug_unique_hash\":\"112o1lw7n05s000000000\",\"field_input_name\":\"fk_license_id\",\"fieldName\":\"License\",\"inputName\":\"fk_license_id\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackartistselect",
    "field_id": 14,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackartistselect\",\"track_trackartistselect_cell\":\"4\",\"field_slug_unique_hash\":\"4w48rjsg7de0000000000\",\"field_input_name\":\"fk_artist_id\",\"fieldName\":\"Artist\",\"inputName\":\"fk_artist_id\"}"
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
    "fk_field_id": "SEO Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4yl3ix1a5280000000000\",\"field_input_name\":\"\",\"fieldName\":\"SEO Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
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
  }
]
JSON;
        return json_decode($json);
    }

}