<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Database\Migrations;

use App\Modules\Core\Library\Migration;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;

class CreateFieldItemsTable_2022_01_05_161811 extends Migration {

    /**
     * @throws \Exception
     */
    public function up()
    {
        $fieldTable = Tables::getTable(Tables::FIELD);
        $this->getDB()->run("
CREATE TABLE IF NOT EXISTS `{$this->tableName()}` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fk_field_id` int(10) unsigned NOT NULL,
  `field_id` bigint(20) unsigned NOT NULL,
  `field_parent_id` bigint(20) unsigned DEFAULT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_options` longtext NOT NULL CHECK (json_valid(`field_options`)),
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `bt_field_items_fk_field_id_foreign` (`fk_field_id`),
  CONSTRAINT `bt_field_items_fk_field_id_foreign` FOREIGN KEY (`fk_field_id`) REFERENCES `$fieldTable` (`field_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`field_options` is null or json_valid(`field_options`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        (new FieldData())->importFieldItems($this->getFieldItemsToImport());
    }

    public function down()
    {
        return $this->dropTable($this->tableName());
    }

    private function tableName(): string
    {
        return Tables::getTable(Tables::FIELD_ITEMS);
    }

    public function getFieldItemsToImport(): array
    {
        $json =<<<'JSON'
[
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"596c407bfb4f4d17bff1b1840c7fcf\",\"fieldName\":\"Post Experience\",\"inputName\":\"post_experience\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3a30ae4e2d2ebf3f3e6b45f35b3445\",\"fieldName\":\"Post Title\",\"inputName\":\"post_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Title Here\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"28d4f12fdd87c72c2217e68f781b7c\",\"fieldName\":\"Post Content\",\"inputName\":\"post_content\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"cd79f31a02ebaf382524255bb9977f\",\"fieldName\":\"Post Settings\",\"inputName\":\"post_settings\",\"row\":\"2\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "media_media-image",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"c37249cd27fa2cf9fb227c48f80a96\",\"fieldName\":\"Featured Image\",\"inputName\":\"image_url\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "post_postcategoryselect",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postcategoryselect\",\"post_postcategoryselect_cell\":\"2\",\"field_slug_unique_hash\":\"98a6eec61986b338a606a293d52641\",\"fieldName\":\"Posts Category\",\"inputName\":\"fk_cat_id\",\"multipleSelect\":\"1\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "post_postauthorselect",
    "field_id": 7,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postauthorselect\",\"post_postauthorselect_cell\":\"3\",\"field_slug_unique_hash\":\"108w14fmdpow000000000\",\"fieldName\":\"Author\",\"inputName\":\"user_id\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"4\",\"field_slug_unique_hash\":\"63xrwrywapc0000000000\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 9,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"qnje9n94xxs000000000\",\"fieldName\":\"Post Slug\",\"inputName\":\"post_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Post Slug (optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_select",
    "field_id": 10,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"48jzxkcjymw0000000000\",\"fieldName\":\"Status\",\"inputName\":\"post_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_date",
    "field_id": 11,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"13mgpl0rp37g000000000\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5dk0d0jv6uk0000000000\",\"fieldName\":\"Category Experience\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"lkrrixgthjk000000000\",\"fieldName\":\"Category Title\",\"inputName\":\"cat_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Title Here\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"1pv65aei9cjk000000000\",\"fieldName\":\"Category Content\",\"inputName\":\"cat_content\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5xxzokq5lo40000000000\",\"fieldName\":\"Category Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "post_postcategoryselect",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postcategoryselect\",\"post_postcategoryselect_cell\":\"1\",\"field_slug_unique_hash\":\"30hrng80bsq0000000000\",\"fieldName\":\"Parent Category\",\"inputName\":\"cat_parent_id\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "modular_rowcolumn",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"2yakqy9yrx00000000000\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_text",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3mcl6jrdna00000000000\",\"fieldName\":\"Category Slug\",\"inputName\":\"cat_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Category Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_select",
    "field_id": 8,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4jv8e05l2co0000000000\",\"fieldName\":\"Category Status\",\"inputName\":\"cat_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "Post Category Page",
    "field_name": "input_date",
    "field_id": 9,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"4ffhzgcapki0000000000\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Experience\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Title\",\"inputName\":\"track_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Track Title Here\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Content\",\"inputName\":\"track_content\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Track Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"74cf7kls1jk0000000000\",\"fieldName\":\"Featured Asset\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "media_media-image",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"5otpehs2q9o0000000000\",\"fieldName\":\"Featured Image\",\"inputName\":\"image_url\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "media_media-audio",
    "field_id": 7,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"media_media-audio\",\"media_media-audio_cell\":\"1\",\"field_slug_unique_hash\":\"5pa7nlu8thk0000000000\",\"fieldName\":\"Featured Audio\",\"inputName\":\"audio_url\",\"featured_audio\":\"\",\"audio_url\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"2hlqfa9tbts0000000000\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_date",
    "field_id": 9,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"35geneeemj60000000000\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_select",
    "field_id": 10,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6zmwyh9ws7c0000000000\",\"fieldName\":\"Status\",\"inputName\":\"track_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 11,
    "field_parent_id": 8,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3f6do5um7eu0000000000\",\"fieldName\":\"Slug\",\"inputName\":\"track_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Track Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackgenreradio",
    "field_id": 12,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackgenreradio\",\"track_trackgenreradio_cell\":\"3\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Genre\",\"inputName\":\"fk_genre_id\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_tracklicenseselect",
    "field_id": 13,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_tracklicenseselect\",\"track_tracklicenseselect_cell\":\"3\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"License\",\"inputName\":\"fk_license_id\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackartistselect",
    "field_id": 14,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackartistselect\",\"track_trackartistselect_cell\":\"4\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Artist\",\"inputName\":\"fk_artist_id\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"fieldName\":\"Page Experience\",\"inputName\":\"page_experience\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"cell\":\"on\",\"groupName\":\"This is a text group\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"input_text_group\":\"This is a text group\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"fieldName\":\"Page Title\",\"inputName\":\"page_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Page Title\",\"readOnly\":\"0\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"input_text_group\":\"This is a text group\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"fieldName\":\"Page Slug\",\"inputName\":\"page_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Page Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_select",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"input_select_group\":\"This is a text group\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"fieldName\":\"Page Status\",\"inputName\":\"page_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4yl3ix1a5280000000000\",\"fieldName\":\"SEO Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"17ols4a28d5s000000000\",\"fieldName\":\"Basic\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"7glk8wuk0aw0000000000\",\"fieldName\":\"Title (Optional)\",\"inputName\":\"seo_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"100\",\"placeholder\":\"Auto-generate from title if empty\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4w1vg0g41i40000000000\",\"fieldName\":\"Description (Optional)\",\"inputName\":\"seo_description\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"250\",\"placeholder\":\"Enter SEO Description\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"14blceu1kl6k000000000\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6ivu9jbpixc0000000000\",\"fieldName\":\"Canonical URL\",\"inputName\":\"seo_canonical_url\",\"textType\":\"url\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Default to content URL\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_select",
    "field_id": 7,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4v28rctp7080000000000\",\"fieldName\":\"Indexing\",\"inputName\":\"seo_indexing\",\"selectData\":\"1:index,0:noindex\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_select",
    "field_id": 8,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4g1g3x0zpe00000000000\",\"fieldName\":\"Following\",\"inputName\":\"seo_following\",\"selectData\":\"1:follow,0:nofollow\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_select",
    "field_id": 9,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"3\",\"field_slug_unique_hash\":\"6fazxbd6gxs0000000000\",\"fieldName\":\"Open Graph Type\",\"inputName\":\"seo_open_graph_type\",\"selectData\":\"article:Article,website:Website\",\"defaultValue\":\"article\"}"
  },
  {
    "fk_field_id": "Site Header",
    "field_name": "menu_menus",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"menu_menus\",\"field_slug_unique_hash\":\"i5ap1q0rukg000000000\",\"fieldName\":\"site_header\",\"inputName\":\"site_header_menu\",\"menuSlug\":\"header-menu:1\",\"displayName\":\"1\"}"
  },
  {
    "fk_field_id": "Site Footer",
    "field_name": "menu_menus",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"menu_menus\",\"field_slug_unique_hash\":\"1zqxqpbnlkhs000000000\",\"fieldName\":\"site_footer\",\"inputName\":\"site_footer_menu\",\"menuSlug\":\"footer-menu:2\",\"displayName\":\"0\"}"
  },
  {
    "fk_field_id": "Sidebar Widget",
    "field_name": "widget_widgets",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"widget_widgets\",\"field_slug_unique_hash\":\"tqtc2pjcx4g000000000\",\"fieldName\":\"sidebar_widget\",\"inputName\":\"sidebar_widget\",\"widgetSlug\":\"sidebar-widget:1\"}"
  },
  {
    "fk_field_id": "Upload App Page",
    "field_name": "media_media-manager",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"media_media-manager\",\"field_slug_unique_hash\":\"6hekf1492880000000000\",\"fieldName\":\"Upload App\",\"inputName\":\"plugin_url\",\"featured_link\":\"\",\"file_url\":\"\"}"
  }
]
JSON;
        return json_decode($json);
    }
}