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
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"596c407bfb4f4d17bff1b1840c7fcf\",\"fieldName\":\"Post Experience\",\"inputName\":\"post_experience\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3a30ae4e2d2ebf3f3e6b45f35b3445\",\"fieldName\":\"Post Title\",\"inputName\":\"post_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Title Here\",\"readOnly\":\"0\",\"required\":\"1\",\"elementWrapper\":\"\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"28d4f12fdd87c72c2217e68f781b7c\",\"fieldName\":\"Post Content\",\"inputName\":\"post_content\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"cd79f31a02ebaf382524255bb9977f\",\"fieldName\":\"Post Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"hideInUserEditForm\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "post_postcategoryselect",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postcategoryselect\",\"post_postcategoryselect_cell\":\"1\",\"field_slug_unique_hash\":\"98a6eec61986b338a606a293d52641\",\"fieldName\":\"Posts Category\",\"inputName\":\"fk_cat_id\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"dcba625cdee3a38703cfbe5d4287e5\",\"fieldName\":\"Post Slug\",\"inputName\":\"post_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Post Slug (optional)\",\"readOnly\":\"0\",\"required\":\"0\",\"elementWrapper\":\"\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "post_postauthorselect",
    "field_id": 7,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postauthorselect\",\"post_postauthorselect_cell\":\"2\",\"field_slug_unique_hash\":\"6954fbc4c9923eb407d1be48d878e8\",\"fieldName\":\"Posts Author\",\"inputName\":\"user_id\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_select",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"3\",\"field_slug_unique_hash\":\"0008d7e266d3961b308756af8e00fa\",\"fieldName\":\"Post Status\",\"inputName\":\"post_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_date",
    "field_id": 9,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"3\",\"field_slug_unique_hash\":\"ea6dca1e7d536f3ad620844ec91ab9\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "media_media-image",
    "field_id": 10,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"4\",\"field_slug_unique_hash\":\"c37249cd27fa2cf9fb227c48f80a96\",\"fieldName\":\"Featured Image\",\"inputName\":\"image_url\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_fieldselection",
    "field_id": 11,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"3wtfw5ix15i0000000000\",\"fieldName\":\"FrontEnd View\",\"inputName\":\"\",\"fieldSlug\":\"app-ninetyseven-single-post-view:49\",\"hideInUserEditForm\":\"1\",\"expandField\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Experience\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Title\",\"inputName\":\"track_title\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"Enter Track Title Here\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Content\",\"inputName\":\"track_content\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Track Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"elementName\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "media_media-image",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Feature Image\",\"inputName\":\"image_url\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "media_media-audio",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-audio\",\"media_media-audio_cell\":\"1\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Featured Audio\",\"inputName\":\"audio_url\",\"featured_audio\":\"\",\"audio_url\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_select",
    "field_id": 7,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"2\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Track Status\",\"inputName\":\"track_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_date",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"2\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"1\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackgenreradio",
    "field_id": 9,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackgenreradio\",\"track_trackgenreradio_cell\":\"3\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Genre\",\"inputName\":\"fk_genre_id\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_tracklicenseselect",
    "field_id": 10,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_tracklicenseselect\",\"track_tracklicenseselect_cell\":\"3\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"License\",\"inputName\":\"fk_license_id\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 11,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"4\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Track Slug\",\"inputName\":\"track_slug\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"Track Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackartistselect",
    "field_id": 12,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackartistselect\",\"track_trackartistselect_cell\":\"4\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Artist\",\"inputName\":\"fk_artist_id\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"fieldName\":\"Page Experience\",\"inputName\":\"page_experience\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"fieldName\":\"Page Title\",\"inputName\":\"page_title\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"Enter Page Title\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"fieldName\":\"Page Slug\",\"inputName\":\"page_slug\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"Page Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "Default Page Field",
    "field_name": "input_select",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4ci4p6m7qxw0000000000\",\"fieldName\":\"Page Status\",\"inputName\":\"page_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"1\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"0\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4yl3ix1a5280000000000\",\"fieldName\":\"SEO Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"rjs1x3ha48g000000000\",\"fieldName\":\"Title (Optional)\",\"inputName\":\"seo_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"100\",\"placeholder\":\"Auto-generate from title if empty\",\"readOnly\":\"0\",\"required\":\"0\",\"elementWrapper\":\"\",\"attributes\":\"\",\"templateEngine\":\"\",\"nativeTemplateHook\":\"\",\"tonicsTemplateFrag\":\"\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6n1be5c9ua40000000000\",\"fieldName\":\"Description (Optional)\",\"inputName\":\"seo_description\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"250\",\"placeholder\":\"Enter SEO Description\",\"readOnly\":\"0\",\"required\":\"0\",\"elementWrapper\":\"\",\"attributes\":\"\",\"templateEngine\":\"\",\"nativeTemplateHook\":\"\",\"tonicsTemplateFrag\":\"\"}"
  }
]
JSON;
        return json_decode($json);
    }
}