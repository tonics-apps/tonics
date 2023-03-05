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
use App\Modules\Field\Data\FieldData;

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
     * @param FieldData $fieldData
     * @param $settingsData
     * @param array $slugs
     * @return string
     * @throws \Exception
     */
    public static function getSettingsHTMLFrag(FieldData $fieldData, $settingsData, array $slugs = []): string
    {
        if (isset($settingsData['_fieldDetails'])){
            $fieldCategories = $fieldData->compareSortAndUpdateFieldItems(json_decode($settingsData['_fieldDetails']));
            $htmlFrag = $fieldData->getUsersFormFrag($fieldCategories);
        } else {
            $htmlFrag =  $fieldData->generateFieldWithFieldSlug(
                $slugs,
                $settingsData
            )->getHTMLFrag();
        }

        return $htmlFrag;
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
        db(onGetDB: function ($db) use ($key, $data) {
            $key = 'App_Settings_' . $key;
            if (isset($data['token'])){
                unset($data['token']);
            }
            $globalTable = Tables::getTable(Tables::GLOBAL);
            $db->insertOnDuplicate(
                $globalTable,
                [
                    'key' => $key,
                    'value' => json_encode($data)
                ],
                ['value']
            );
        });
        AppConfig::updateRestartService();
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
        $updates = null;

        db(onGetDB: function ($db) use ($key, $globalTable, &$updates) {
            try {
                $updates = $db->row("SELECT * FROM $globalTable WHERE `key` = ?", $key);
            } catch (\Exception $exception){
                $updates = [];
            }

        });

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
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 12,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2nnbe60y6140000000000\",\"field_input_name\":\"post_excerpt\",\"fieldName\":\"Excerpt\",\"inputName\":\"post_excerpt\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"500\",\"placeholder\":\"Enter Post Excerpt\",\"readOnly\":\"0\",\"required\":\"0\"}"
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
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"33mogqqpskw0000000000\",\"field_input_name\":\"OEmbed_width\",\"fieldName\":\"Width (Optional\",\"inputName\":\"OEmbed_width\",\"textType\":\"number\",\"defaultValue\":\"600\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"OEmbed Width (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "oEmbed",
    "field_name": "input_text",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"4p1qor96y600000000000\",\"field_input_name\":\"OEmbed_height\",\"fieldName\":\"Height (Optional)\",\"inputName\":\"OEmbed_height\",\"textType\":\"number\",\"defaultValue\":\"600\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"OEmbed Height (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
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
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"2\",\"field_slug_unique_hash\":\"3aq5iq8e950000000000\",\"field_input_name\":\"track_default_filter_keys\",\"fieldName\":\"Keys\",\"inputName\":\"track_default_filter_keys\",\"selectData\":\"A:A Major (A),Am:A minor (Am),Bb:Bb Major (Bb),Bm:B minor (Bb),C:C Major (C),Cm:C minor (Cm),C#:C# Major (C#),C#m:C# minor (C#m),D:D Major (D),Dm:D minor (Dm),Eb:Eb Major (Eb),Ebm:Eb minor (Ebm),E:E Major (E),Em:E minor (Em),F:F Major (F),Fm:F minor (Fm),F#:F# Major (F#),F#m:F# minor (F#m),G:G Major (G),Gm:G minor (Gm),G#:G# Major (G#),G#m:G# minor (G#m)\",\"defaultValue\":\"\"}"
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
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"4\",\"field_slug_unique_hash\":\"f1rrmva09uw000000000\",\"field_input_name\":\"track_default_filter_mood\",\"fieldName\":\"Mood\",\"inputName\":\"track_default_filter_mood\",\"selectData\":\"No Mood,Atmospheric,Dark,Dreamy,Emotional,Energetic,Exotic,Funky,Happy,Hopeful,Hypnotic,Intense,Intimate,Melancholic,Mellow, Mysterious,Nostalgic,Passionate,Peaceful,Playful,Reflective, Relaxed,Raw,Sad,Sensual,Soulful,Triumphant,Uplifting\",\"defaultValue\":\"\"}"
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
    "field_options": "{\"field_slug\":\"input_choices\",\"input_choices_cell\":\"5\",\"field_slug_unique_hash\":\"40tgb29l7ru0000000000\",\"field_input_name\":\"track_default_filter_acapella_effects\",\"fieldName\":\"Effects\",\"inputName\":\"track_default_filter_acapella_effects\",\"choiceType\":\"checkbox\",\"choices\":\"No Autotune:No Autotune,Autotune:Autotune,Chorus:Chorus,Delay:Delay,Echo:Echo,Flanger:Flanger,Harmony:Harmony,Phaser:Phaser,Reverb:Reverb,Vibrato:Vibrato,Distortion:Distortion,Pitch shift:Pitch shift,Compression:Compression,EQ:EQ,Filtering:Filtering,Volume:Volume,Wah-wah:Wah-wah\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"ripq3o1x5wg000000000\",\"field_input_name\":\"tonics_payment_settings\",\"fieldName\":\"Payment Settings\",\"inputName\":\"tonics_payment_settings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"23m83nv9jow0000000000\",\"field_input_name\":\"\",\"fieldName\":\"PayPal\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "input_select",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"22o02hwk6r6o000000000\",\"field_input_name\":\"tonics_payment_settings_paypal_live\",\"fieldName\":\"Live\",\"inputName\":\"tonics_payment_settings_paypal_live\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2nd15m50dge000000000\",\"field_input_name\":\"tonics_payment_settings_apiCredentials\",\"fieldName\":\"API Credentials\",\"inputName\":\"tonics_payment_settings_apiCredentials\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2aqnpqv5d0g0000000000\",\"field_input_name\":\"tonics_payment_settings_apiCredentials_Live\",\"fieldName\":\"Live\",\"inputName\":\"tonics_payment_settings_apiCredentials_Live\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"16y4mevyvts0000000000\",\"field_input_name\":\"tonics_payment_settings_apiCredentials_Live_ClientID\",\"fieldName\":\"Client ID\",\"inputName\":\"tonics_payment_settings_apiCredentials_Live_ClientID\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Live Client ID\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "input_text",
    "field_id": 7,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6thpge1ykk4000000000\",\"field_input_name\":\"tonics_payment_settings_apiCredentials_Live_SecretKey\",\"fieldName\":\"Secret Key\",\"inputName\":\"tonics_payment_settings_apiCredentials_Live_SecretKey\",\"textType\":\"password\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Live Secret Key\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5yo9mezk9wg0000000000\",\"field_input_name\":\"\",\"fieldName\":\"SandBox\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "input_text",
    "field_id": 9,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4pf2o4lbozy0000000000\",\"field_input_name\":\"tonics_payment_settings_apiCredentials_SandBox_ClientID\",\"fieldName\":\"Client ID\",\"inputName\":\"tonics_payment_settings_apiCredentials_SandBox_ClientID\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter SandBox Client ID\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "input_text",
    "field_id": 10,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"370wbu42k680000000000\",\"field_input_name\":\"tonics_payment_settings_apiCredentials_SandBox_SecretKey\",\"fieldName\":\"Secret Key\",\"inputName\":\"tonics_payment_settings_apiCredentials_SandBox_SecretKey\",\"textType\":\"password\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter SandBox Secret Key\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 11,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"26d80lb119mo000000000\",\"field_input_name\":\"\",\"fieldName\":\"WebHook\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "input_text",
    "field_id": 12,
    "field_parent_id": 11,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2ncyfufaq2q0000000000\",\"field_input_name\":\"tonics_payment_settings_apiCredentials_WebHook_ID\",\"fieldName\":\"WebHook ID\",\"inputName\":\"tonics_payment_settings_apiCredentials_WebHook_ID\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"WebHook ID as configured in your Developer Portal account.\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 13,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1740l7efx9ds000000000\",\"field_input_name\":\"\",\"fieldName\":\"FlutterWave\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Payment Settings",
    "field_name": "input_text",
    "field_id": 14,
    "field_parent_id": 13,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3vbomfz02by0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Coming Soon\",\"inputName\":\"\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Coming Soon\",\"readOnly\":\"1\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"44knlwmejgw0000000000\",\"field_input_name\":\"tonics_core_settings\",\"fieldName\":\"Core Settings\",\"inputName\":\"tonics_core_settings\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"7gp0hhha16w0000000000\",\"field_input_name\":\"tonics_core_settings_AppSettingsContainer\",\"fieldName\":\"App Settiings\",\"inputName\":\"tonics_core_settings_AppSettingsContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"bt2f0hztu1k000000000\",\"field_input_name\":\"tonics_core_settings_appSettings_appName\",\"fieldName\":\"App Name\",\"inputName\":\"tonics_core_settings_appSettings_appName\",\"textType\":\"text\",\"defaultValue\":\"Tonics\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter App Name\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6lp62m5u2gg0000000000\",\"field_input_name\":\"tonics_core_settings_appSettings_appURL\",\"fieldName\":\"App URL\",\"inputName\":\"tonics_core_settings_appSettings_appURL\",\"textType\":\"url\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter App URL\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_select",
    "field_id": 5,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4on0p0yfxoo0000000000\",\"field_input_name\":\"tonics_core_settings_appSettings_appTimeZone\",\"fieldName\":\"App TimeZone\",\"inputName\":\"tonics_core_settings_appSettings_appTimeZone\",\"selectData\":\"Africa/Abidjan:Africa/Abidjan,Africa/Accra:Africa/Accra,Africa/Addis_Ababa:Africa/Addis_Ababa,Africa/Algiers:Africa/Algiers,Africa/Asmara:Africa/Asmara,Africa/Bamako:Africa/Bamako,Africa/Bangui:Africa/Bangui,Africa/Banjul:Africa/Banjul,Africa/Bissau:Africa/Bissau,Africa/Blantyre:Africa/Blantyre,Africa/Brazzaville:Africa/Brazzaville,Africa/Bujumbura:Africa/Bujumbura,Africa/Cairo:Africa/Cairo,Africa/Casablanca:Africa/Casablanca,Africa/Ceuta:Africa/Ceuta,Africa/Conakry:Africa/Conakry,Africa/Dakar:Africa/Dakar,Africa/Dar_es_Salaam:Africa/Dar_es_Salaam,Africa/Djibouti:Africa/Djibouti,Africa/Douala:Africa/Douala,Africa/El_Aaiun:Africa/El_Aaiun,Africa/Freetown:Africa/Freetown,Africa/Gaborone:Africa/Gaborone,Africa/Harare:Africa/Harare,Africa/Johannesburg:Africa/Johannesburg,Africa/Juba:Africa/Juba,Africa/Kampala:Africa/Kampala,Africa/Khartoum:Africa/Khartoum,Africa/Kigali:Africa/Kigali,Africa/Kinshasa:Africa/Kinshasa,Africa/Lagos:Africa/Lagos,Africa/Libreville:Africa/Libreville,Africa/Lome:Africa/Lome,Africa/Luanda:Africa/Luanda,Africa/Lubumbashi:Africa/Lubumbashi,Africa/Lusaka:Africa/Lusaka,Africa/Malabo:Africa/Malabo,Africa/Maputo:Africa/Maputo,Africa/Maseru:Africa/Maseru,Africa/Mbabane:Africa/Mbabane,Africa/Mogadishu:Africa/Mogadishu,Africa/Monrovia:Africa/Monrovia,Africa/Nairobi:Africa/Nairobi,Africa/Ndjamena:Africa/Ndjamena,Africa/Niamey:Africa/Niamey,Africa/Nouakchott:Africa/Nouakchott,Africa/Ouagadougou:Africa/Ouagadougou,Africa/Porto-Novo:Africa/Porto-Novo,Africa/Sao_Tome:Africa/Sao_Tome,Africa/Tripoli:Africa/Tripoli,Africa/Tunis:Africa/Tunis,Africa/Windhoek:Africa/Windhoek,America/Adak:America/Adak,America/Anchorage:America/Anchorage,America/Anguilla:America/Anguilla,America/Antigua:America/Antigua,America/Araguaina:America/Araguaina,America/Argentina/Buenos_Aires:America/Argentina/Buenos_Aires,America/Argentina/Catamarca:America/Argentina/Catamarca,America/Argentina/Cordoba:America/Argentina/Cordoba,America/Argentina/Jujuy:America/Argentina/Jujuy,America/Argentina/La_Rioja:America/Argentina/La_Rioja,America/Argentina/Mendoza:America/Argentina/Mendoza,America/Argentina/Rio_Gallegos:America/Argentina/Rio_Gallegos,America/Argentina/Salta:America/Argentina/Salta,America/Argentina/San_Juan:America/Argentina/San_Juan,America/Argentina/San_Luis:America/Argentina/San_Luis,America/Argentina/Tucuman:America/Argentina/Tucuman,America/Argentina/Ushuaia:America/Argentina/Ushuaia,America/Aruba:America/Aruba,America/Asuncion:America/Asuncion,America/Atikokan:America/Atikokan,America/Bahia:America/Bahia,America/Bahia_Banderas:America/Bahia_Banderas,America/Barbados:America/Barbados,America/Belem:America/Belem,America/Belize:America/Belize,America/Blanc-Sablon:America/Blanc-Sablon,America/Boa_Vista:America/Boa_Vista,America/Bogota:America/Bogota,America/Boise:America/Boise,America/Cambridge_Bay:America/Cambridge_Bay,America/Campo_Grande:America/Campo_Grande,America/Cancun:America/Cancun,America/Caracas:America/Caracas,America/Cayenne:America/Cayenne,America/Cayman:America/Cayman,America/Chicago:America/Chicago,America/Chihuahua:America/Chihuahua,America/Costa_Rica:America/Costa_Rica,America/Creston:America/Creston,America/Cuiaba:America/Cuiaba,America/Curacao:America/Curacao,America/Danmarkshavn:America/Danmarkshavn,America/Dawson:America/Dawson,America/Dawson_Creek:America/Dawson_Creek,America/Denver:America/Denver,America/Detroit:America/Detroit,America/Dominica:America/Dominica,America/Edmonton:America/Edmonton,America/Eirunepe:America/Eirunepe,America/El_Salvador:America/El_Salvador,America/Fort_Nelson:America/Fort_Nelson,America/Fortaleza:America/Fortaleza,America/Glace_Bay:America/Glace_Bay,America/Goose_Bay:America/Goose_Bay,America/Grand_Turk:America/Grand_Turk,America/Grenada:America/Grenada,America/Guadeloupe:America/Guadeloupe,America/Guatemala:America/Guatemala,America/Guayaquil:America/Guayaquil,America/Guyana:America/Guyana,America/Halifax:America/Halifax,America/Havana:America/Havana,America/Hermosillo:America/Hermosillo,America/Indiana/Indianapolis:America/Indiana/Indianapolis,America/Indiana/Knox:America/Indiana/Knox,America/Indiana/Marengo:America/Indiana/Marengo,America/Indiana/Petersburg:America/Indiana/Petersburg,America/Indiana/Tell_City:America/Indiana/Tell_City,America/Indiana/Vevay:America/Indiana/Vevay,America/Indiana/Vincennes:America/Indiana/Vincennes,America/Indiana/Winamac:America/Indiana/Winamac,America/Inuvik:America/Inuvik,America/Iqaluit:America/Iqaluit,America/Jamaica:America/Jamaica,America/Juneau:America/Juneau,America/Kentucky/Louisville:America/Kentucky/Louisville,America/Kentucky/Monticello:America/Kentucky/Monticello,America/Kralendijk:America/Kralendijk,America/La_Paz:America/La_Paz,America/Lima:America/Lima,America/Los_Angeles:America/Los_Angeles,America/Lower_Princes:America/Lower_Princes,America/Maceio:America/Maceio,America/Managua:America/Managua,America/Manaus:America/Manaus,America/Marigot:America/Marigot,America/Martinique:America/Martinique,America/Matamoros:America/Matamoros,America/Mazatlan:America/Mazatlan,America/Menominee:America/Menominee,America/Merida:America/Merida,America/Metlakatla:America/Metlakatla,America/Mexico_City:America/Mexico_City,America/Miquelon:America/Miquelon,America/Moncton:America/Moncton,America/Monterrey:America/Monterrey,America/Montevideo:America/Montevideo,America/Montserrat:America/Montserrat,America/Nassau:America/Nassau,America/New_York:America/New_York,America/Nipigon:America/Nipigon,America/Nome:America/Nome,America/Noronha:America/Noronha,America/North_Dakota/Beulah:America/North_Dakota/Beulah,America/North_Dakota/Center:America/North_Dakota/Center,America/North_Dakota/New_Salem:America/North_Dakota/New_Salem,America/Nuuk:America/Nuuk,America/Ojinaga:America/Ojinaga,America/Panama:America/Panama,America/Pangnirtung:America/Pangnirtung,America/Paramaribo:America/Paramaribo,America/Phoenix:America/Phoenix,America/Port-au-Prince:America/Port-au-Prince,America/Port_of_Spain:America/Port_of_Spain,America/Porto_Velho:America/Porto_Velho,America/Puerto_Rico:America/Puerto_Rico,America/Punta_Arenas:America/Punta_Arenas,America/Rainy_River:America/Rainy_River,America/Rankin_Inlet:America/Rankin_Inlet,America/Recife:America/Recife,America/Regina:America/Regina,America/Resolute:America/Resolute,America/Rio_Branco:America/Rio_Branco,America/Santarem:America/Santarem,America/Santiago:America/Santiago,America/Santo_Domingo:America/Santo_Domingo,America/Sao_Paulo:America/Sao_Paulo,America/Scoresbysund:America/Scoresbysund,America/Sitka:America/Sitka,America/St_Barthelemy:America/St_Barthelemy,America/St_Johns:America/St_Johns,America/St_Kitts:America/St_Kitts,America/St_Lucia:America/St_Lucia,America/St_Thomas:America/St_Thomas,America/St_Vincent:America/St_Vincent,America/Swift_Current:America/Swift_Current,America/Tegucigalpa:America/Tegucigalpa,America/Thule:America/Thule,America/Thunder_Bay:America/Thunder_Bay,America/Tijuana:America/Tijuana,America/Toronto:America/Toronto,America/Tortola:America/Tortola,America/Vancouver:America/Vancouver,America/Whitehorse:America/Whitehorse,America/Winnipeg:America/Winnipeg,America/Yakutat:America/Yakutat,America/Yellowknife:America/Yellowknife,Antarctica/Casey:Antarctica/Casey,Antarctica/Davis:Antarctica/Davis,Antarctica/DumontDUrville:Antarctica/DumontDUrville,Antarctica/Macquarie:Antarctica/Macquarie,Antarctica/Mawson:Antarctica/Mawson,Antarctica/McMurdo:Antarctica/McMurdo,Antarctica/Palmer:Antarctica/Palmer,Antarctica/Rothera:Antarctica/Rothera,Antarctica/Syowa:Antarctica/Syowa,Antarctica/Troll:Antarctica/Troll,Antarctica/Vostok:Antarctica/Vostok,Arctic/Longyearbyen:Arctic/Longyearbyen,Asia/Aden:Asia/Aden,Asia/Almaty:Asia/Almaty,Asia/Amman:Asia/Amman,Asia/Anadyr:Asia/Anadyr,Asia/Aqtau:Asia/Aqtau,Asia/Aqtobe:Asia/Aqtobe,Asia/Ashgabat:Asia/Ashgabat,Asia/Atyrau:Asia/Atyrau,Asia/Baghdad:Asia/Baghdad,Asia/Bahrain:Asia/Bahrain,Asia/Baku:Asia/Baku,Asia/Bangkok:Asia/Bangkok,Asia/Barnaul:Asia/Barnaul,Asia/Beirut:Asia/Beirut,Asia/Bishkek:Asia/Bishkek,Asia/Brunei:Asia/Brunei,Asia/Chita:Asia/Chita,Asia/Choibalsan:Asia/Choibalsan,Asia/Colombo:Asia/Colombo,Asia/Damascus:Asia/Damascus,Asia/Dhaka:Asia/Dhaka,Asia/Dili:Asia/Dili,Asia/Dubai:Asia/Dubai,Asia/Dushanbe:Asia/Dushanbe,Asia/Famagusta:Asia/Famagusta,Asia/Gaza:Asia/Gaza,Asia/Hebron:Asia/Hebron,Asia/Ho_Chi_Minh:Asia/Ho_Chi_Minh,Asia/Hong_Kong:Asia/Hong_Kong,Asia/Hovd:Asia/Hovd,Asia/Irkutsk:Asia/Irkutsk,Asia/Jakarta:Asia/Jakarta,Asia/Jayapura:Asia/Jayapura,Asia/Jerusalem:Asia/Jerusalem,Asia/Kabul:Asia/Kabul,Asia/Kamchatka:Asia/Kamchatka,Asia/Karachi:Asia/Karachi,Asia/Kathmandu:Asia/Kathmandu,Asia/Khandyga:Asia/Khandyga,Asia/Kolkata:Asia/Kolkata,Asia/Krasnoyarsk:Asia/Krasnoyarsk,Asia/Kuala_Lumpur:Asia/Kuala_Lumpur,Asia/Kuching:Asia/Kuching,Asia/Kuwait:Asia/Kuwait,Asia/Macau:Asia/Macau,Asia/Magadan:Asia/Magadan,Asia/Makassar:Asia/Makassar,Asia/Manila:Asia/Manila,Asia/Muscat:Asia/Muscat,Asia/Nicosia:Asia/Nicosia,Asia/Novokuznetsk:Asia/Novokuznetsk,Asia/Novosibirsk:Asia/Novosibirsk,Asia/Omsk:Asia/Omsk,Asia/Oral:Asia/Oral,Asia/Phnom_Penh:Asia/Phnom_Penh,Asia/Pontianak:Asia/Pontianak,Asia/Pyongyang:Asia/Pyongyang,Asia/Qatar:Asia/Qatar,Asia/Qostanay:Asia/Qostanay,Asia/Qyzylorda:Asia/Qyzylorda,Asia/Riyadh:Asia/Riyadh,Asia/Sakhalin:Asia/Sakhalin,Asia/Samarkand:Asia/Samarkand,Asia/Seoul:Asia/Seoul,Asia/Shanghai:Asia/Shanghai,Asia/Singapore:Asia/Singapore,Asia/Srednekolymsk:Asia/Srednekolymsk,Asia/Taipei:Asia/Taipei,Asia/Tashkent:Asia/Tashkent,Asia/Tbilisi:Asia/Tbilisi,Asia/Tehran:Asia/Tehran,Asia/Thimphu:Asia/Thimphu,Asia/Tokyo:Asia/Tokyo,Asia/Tomsk:Asia/Tomsk,Asia/Ulaanbaatar:Asia/Ulaanbaatar,Asia/Urumqi:Asia/Urumqi,Asia/Ust-Nera:Asia/Ust-Nera,Asia/Vientiane:Asia/Vientiane,Asia/Vladivostok:Asia/Vladivostok,Asia/Yakutsk:Asia/Yakutsk,Asia/Yangon:Asia/Yangon,Asia/Yekaterinburg:Asia/Yekaterinburg,Asia/Yerevan:Asia/Yerevan,Atlantic/Azores:Atlantic/Azores,Atlantic/Bermuda:Atlantic/Bermuda,Atlantic/Canary:Atlantic/Canary,Atlantic/Cape_Verde:Atlantic/Cape_Verde,Atlantic/Faroe:Atlantic/Faroe,Atlantic/Madeira:Atlantic/Madeira,Atlantic/Reykjavik:Atlantic/Reykjavik,Atlantic/South_Georgia:Atlantic/South_Georgia,Atlantic/St_Helena:Atlantic/St_Helena,Atlantic/Stanley:Atlantic/Stanley,Australia/Adelaide:Australia/Adelaide,Australia/Brisbane:Australia/Brisbane,Australia/Broken_Hill:Australia/Broken_Hill,Australia/Darwin:Australia/Darwin,Australia/Eucla:Australia/Eucla,Australia/Hobart:Australia/Hobart,Australia/Lindeman:Australia/Lindeman,Australia/Lord_Howe:Australia/Lord_Howe,Australia/Melbourne:Australia/Melbourne,Australia/Perth:Australia/Perth,Australia/Sydney:Australia/Sydney,Europe/Amsterdam:Europe/Amsterdam,Europe/Andorra:Europe/Andorra,Europe/Astrakhan:Europe/Astrakhan,Europe/Athens:Europe/Athens,Europe/Belgrade:Europe/Belgrade,Europe/Berlin:Europe/Berlin,Europe/Bratislava:Europe/Bratislava,Europe/Brussels:Europe/Brussels,Europe/Bucharest:Europe/Bucharest,Europe/Budapest:Europe/Budapest,Europe/Busingen:Europe/Busingen,Europe/Chisinau:Europe/Chisinau,Europe/Copenhagen:Europe/Copenhagen,Europe/Dublin:Europe/Dublin,Europe/Gibraltar:Europe/Gibraltar,Europe/Guernsey:Europe/Guernsey,Europe/Helsinki:Europe/Helsinki,Europe/Isle_of_Man:Europe/Isle_of_Man,Europe/Istanbul:Europe/Istanbul,Europe/Jersey:Europe/Jersey,Europe/Kaliningrad:Europe/Kaliningrad,Europe/Kiev:Europe/Kiev,Europe/Kirov:Europe/Kirov,Europe/Lisbon:Europe/Lisbon,Europe/Ljubljana:Europe/Ljubljana,Europe/London:Europe/London,Europe/Luxembourg:Europe/Luxembourg,Europe/Madrid:Europe/Madrid,Europe/Malta:Europe/Malta,Europe/Mariehamn:Europe/Mariehamn,Europe/Minsk:Europe/Minsk,Europe/Monaco:Europe/Monaco,Europe/Moscow:Europe/Moscow,Europe/Oslo:Europe/Oslo,Europe/Paris:Europe/Paris,Europe/Podgorica:Europe/Podgorica,Europe/Prague:Europe/Prague,Europe/Riga:Europe/Riga,Europe/Rome:Europe/Rome,Europe/Samara:Europe/Samara,Europe/San_Marino:Europe/San_Marino,Europe/Sarajevo:Europe/Sarajevo,Europe/Saratov:Europe/Saratov,Europe/Simferopol:Europe/Simferopol,Europe/Skopje:Europe/Skopje,Europe/Sofia:Europe/Sofia,Europe/Stockholm:Europe/Stockholm,Europe/Tallinn:Europe/Tallinn,Europe/Tirane:Europe/Tirane,Europe/Ulyanovsk:Europe/Ulyanovsk,Europe/Uzhgorod:Europe/Uzhgorod,Europe/Vaduz:Europe/Vaduz,Europe/Vatican:Europe/Vatican,Europe/Vienna:Europe/Vienna,Europe/Vilnius:Europe/Vilnius,Europe/Volgograd:Europe/Volgograd,Europe/Warsaw:Europe/Warsaw,Europe/Zagreb:Europe/Zagreb,Europe/Zaporozhye:Europe/Zaporozhye,Europe/Zurich:Europe/Zurich,Indian/Antananarivo:Indian/Antananarivo,Indian/Chagos:Indian/Chagos,Indian/Christmas:Indian/Christmas,Indian/Cocos:Indian/Cocos,Indian/Comoro:Indian/Comoro,Indian/Kerguelen:Indian/Kerguelen,Indian/Mahe:Indian/Mahe,Indian/Maldives:Indian/Maldives,Indian/Mauritius:Indian/Mauritius,Indian/Mayotte:Indian/Mayotte,Indian/Reunion:Indian/Reunion,Pacific/Apia:Pacific/Apia,Pacific/Auckland:Pacific/Auckland,Pacific/Bougainville:Pacific/Bougainville,Pacific/Chatham:Pacific/Chatham,Pacific/Chuuk:Pacific/Chuuk,Pacific/Easter:Pacific/Easter,Pacific/Efate:Pacific/Efate,Pacific/Enderbury:Pacific/Enderbury,Pacific/Fakaofo:Pacific/Fakaofo,Pacific/Fiji:Pacific/Fiji,Pacific/Funafuti:Pacific/Funafuti,Pacific/Galapagos:Pacific/Galapagos,Pacific/Gambier:Pacific/Gambier,Pacific/Guadalcanal:Pacific/Guadalcanal,Pacific/Guam:Pacific/Guam,Pacific/Honolulu:Pacific/Honolulu,Pacific/Kiritimati:Pacific/Kiritimati,Pacific/Kosrae:Pacific/Kosrae,Pacific/Kwajalein:Pacific/Kwajalein,Pacific/Majuro:Pacific/Majuro,Pacific/Marquesas:Pacific/Marquesas,Pacific/Midway:Pacific/Midway,Pacific/Nauru:Pacific/Nauru,Pacific/Niue:Pacific/Niue,Pacific/Norfolk:Pacific/Norfolk,Pacific/Noumea:Pacific/Noumea,Pacific/Pago_Pago:Pacific/Pago_Pago,Pacific/Palau:Pacific/Palau,Pacific/Pitcairn:Pacific/Pitcairn,Pacific/Pohnpei:Pacific/Pohnpei,Pacific/Port_Moresby:Pacific/Port_Moresby,Pacific/Rarotonga:Pacific/Rarotonga,Pacific/Saipan:Pacific/Saipan,Pacific/Tahiti:Pacific/Tahiti,Pacific/Tarawa:Pacific/Tarawa,Pacific/Tongatapu:Pacific/Tongatapu,Pacific/Wake:Pacific/Wake,Pacific/Wallis:Pacific/Wallis,UTC:UTC\",\"defaultValue\":\"Africa/Lagos\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_select",
    "field_id": 6,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3qeoqmilg5g0000000000\",\"field_input_name\":\"tonics_core_settings_appSettings_appLog404\",\"fieldName\":\"App Log 404\",\"inputName\":\"tonics_core_settings_appSettings_appLog404\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_select",
    "field_id": 7,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"qicv7p617o0000000000\",\"field_input_name\":\"tonics_core_settings_appSettings_appEnvironment\",\"fieldName\":\"App Environment\",\"inputName\":\"tonics_core_settings_appSettings_appEnvironment\",\"selectData\":\"dev:Development,production:Production\",\"defaultValue\":\"production\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 8,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2jhxm3bsgaa0000000000\",\"field_input_name\":\"tonics_core_settings_MailContainer\",\"fieldName\":\"Mail\",\"inputName\":\"tonics_core_settings_MailContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_select",
    "field_id": 9,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"197tpaz4z7z4000000000\",\"field_input_name\":\"tonics_core_settings_mail_mailer\",\"fieldName\":\"Mailer\",\"inputName\":\"tonics_core_settings_mail_mailer\",\"selectData\":\"smtp:SMTP,mail:Mail\",\"defaultValue\":\"smtp\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 10,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5bahfs7lacc0000000000\",\"field_input_name\":\"tonics_core_settings_mail_mailHost\",\"fieldName\":\"Mail Host\",\"inputName\":\"tonics_core_settings_mail_mailHost\",\"textType\":\"text\",\"defaultValue\":\"localhost\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Mail Host\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 11,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"vu4u92a7k9c000000000\",\"field_input_name\":\"tonics_core_settings_mail_mailPort\",\"fieldName\":\"Mail Port\",\"inputName\":\"tonics_core_settings_mail_mailPort\",\"textType\":\"number\",\"defaultValue\":\"25\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Mail Port\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 12,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"bomggq9rals000000000\",\"field_input_name\":\"tonics_core_settings_mail_mailUsername\",\"fieldName\":\"Mail Username\",\"inputName\":\"tonics_core_settings_mail_mailUsername\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Mail Username\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 13,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3vuuf9kzvy80000000000\",\"field_input_name\":\"tonics_core_settings_mail_mailPort\",\"fieldName\":\"Mail Password\",\"inputName\":\"tonics_core_settings_mail_mailPassword\",\"textType\":\"password\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Mail Password\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_select",
    "field_id": 14,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2kubph2w8ly0000000000\",\"field_input_name\":\"tonics_core_settings_mail_mailEncryption\",\"fieldName\":\"Mail Encryption\",\"inputName\":\"tonics_core_settings_mail_mailEncryption\",\"selectData\":\"tls:TLS,ssl:SSL,no:No Encryption\",\"defaultValue\":\"tls\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 15,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5tuq7nrp3280000000000\",\"field_input_name\":\"tonics_core_settings_mail_mailFromAddress\",\"fieldName\":\"MAIL FROM ADDRESS\",\"inputName\":\"tonics_core_settings_mail_mailFromAddress\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter The Address You Want To Send From\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 16,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2i9hjb9eclk0000000000\",\"field_input_name\":\"tonics_core_settings_mail_mailReplyTo\",\"fieldName\":\"MAIL REPLY TO\",\"inputName\":\"tonics_core_settings_mail_mailReplyTo\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter The Address You Want To Reply From\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 17,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5gjx50opx980000000000\",\"field_input_name\":\"tonics_core_settings_UpdatesContainer\",\"fieldName\":\"Updates\",\"inputName\":\"tonics_core_settings_UpdatesContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_select",
    "field_id": 18,
    "field_parent_id": 17,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2g2i3gapeuas000000000\",\"field_input_name\":\"tonics_core_settings_updates_autoUpdateModules\",\"fieldName\":\"Auto Update Modules\",\"inputName\":\"tonics_core_settings_updates_autoUpdateModules\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_select",
    "field_id": 19,
    "field_parent_id": 17,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1nmzqkf0pbcw000000000\",\"field_input_name\":\"tonics_core_settings_updates_autoUpdateApps\",\"fieldName\":\"Auto Update Apps\",\"inputName\":\"tonics_core_settings_updates_autoUpdateApps\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 20,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5w3edftqzbk0000000000\",\"field_input_name\":\"tonics_core_settings_MediaDrivesContainer\",\"fieldName\":\"Media Drives\",\"inputName\":\"tonics_core_settings_MediaDrivesContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "modular_rowcolumnrepeater",
    "field_id": 21,
    "field_parent_id": 20,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"51ielx9gb2k0000000000\",\"field_input_name\":\"tonics_core_settings_mediaDrives_dropBoxRepeater\",\"fieldName\":\"DropBox\",\"inputName\":\"tonics_core_settings_mediaDrives_dropBoxRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Add New DropBox Key\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 22,
    "field_parent_id": 21,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2iwfmemy1zy0000000000\",\"field_input_name\":\"tonics_core_settings_mediaDrives_dropBoxRepeater_Name[]\",\"fieldName\":\"DropBox Name\",\"inputName\":\"tonics_core_settings_mediaDrives_dropBoxRepeater_Name[]\",\"textType\":\"text\",\"defaultValue\":\"DropBox Instance 1\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter DropBox Name\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 23,
    "field_parent_id": 21,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"636viit00k40000000000\",\"field_input_name\":\"tonics_core_settings_mediaDrives_dropBoxRepeater_Key[]\",\"fieldName\":\"DropBox Key\",\"inputName\":\"tonics_core_settings_mediaDrives_dropBoxRepeater_Key[]\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter DropBox Key\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "modular_rowcolumnrepeater",
    "field_id": 24,
    "field_parent_id": 20,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"5a2eu7nrrrk0000000000\",\"field_input_name\":\"tonics_core_settings_mediaDrives_oneDriveRepeater\",\"fieldName\":\"OneDrive\",\"inputName\":\"tonics_core_settings_mediaDrives_oneDriveRepeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"1\",\"repeat_button_text\":\"Add New OneDrive\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Core Settings",
    "field_name": "input_text",
    "field_id": 25,
    "field_parent_id": 24,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1w565xz9kqhs000000000\",\"field_input_name\":\"\",\"fieldName\":\"Comiing Soon\",\"inputName\":\"\",\"textType\":\"text\",\"defaultValue\":\"Coming Soon\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"1\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Track Page Import Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6o0ktvw02xg000000000\",\"field_input_name\":\"\",\"fieldName\":\"Track Page Import Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page Import Settings",
    "field_name": "media_media-manager",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"media_media-manager\",\"media_media-manager_cell\":\"1\",\"field_slug_unique_hash\":\"772ag0bznts0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Import File (Local JSON)\",\"inputName\":\"track_page_import_file_URL\",\"featured_link\":\"\",\"file_url\":\"\"}"
  },
  {
    "fk_field_id": "Track Page Import Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"3f64ul4us160000000000\",\"field_input_name\":\"\",\"fieldName\":\"Import JSON Text\",\"inputName\":\"track_page_import_text\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Paste JSON text\",\"readOnly\":\"0\",\"required\":\"0\"}"
  }
]
JSON;
        return json_decode($json);
    }

}