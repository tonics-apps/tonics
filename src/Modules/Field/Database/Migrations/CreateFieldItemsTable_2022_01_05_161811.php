<?php

/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
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
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"25bpcn9v8mo0000000000\",\"fieldName\":\"Post Experience\",\"inputName\":\"post_experience\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"\",\"attributes\":\"\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"25bpcn9v8mo0000000000\",\"fieldName\":\"Post Title\",\"inputName\":\"post_title\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"Enter Title Here\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"25bpcn9v8mo0000000000\",\"fieldName\":\"Post Content\",\"inputName\":\"post_content\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5y4ebojco200000000000\",\"fieldName\":\"Post Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"elementName\":\"\",\"attributes\":\"\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "post_postcategoryselect",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postcategoryselect\",\"post_postcategoryselect_cell\":\"1\",\"field_slug_unique_hash\":\"5y4ebojco200000000000\",\"fieldName\":\"Posts Category\",\"inputName\":\"fk_cat_id\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"5y4ebojco200000000000\",\"fieldName\":\"Post Slug\",\"inputName\":\"post_slug\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"Post Slug (optional)\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "post_postauthorselect",
    "field_id": 7,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"post_postauthorselect\",\"post_postauthorselect_cell\":\"2\",\"field_slug_unique_hash\":\"5y4ebojco200000000000\",\"fieldName\":\"Posts Author\",\"inputName\":\"user_id\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_select",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"3\",\"field_slug_unique_hash\":\"5y4ebojco200000000000\",\"fieldName\":\"Post Status\",\"inputName\":\"post_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "input_date",
    "field_id": 9,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"3\",\"field_slug_unique_hash\":\"5y4ebojco200000000000\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Post Page",
    "field_name": "media_media-image",
    "field_id": 10,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"4\",\"field_slug_unique_hash\":\"5y4ebojco200000000000\",\"fieldName\":\"Featured Image\",\"inputName\":\"image_url\",\"featured_image\":\"\",\"defaultImage\":\"\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Experience\",\"row\":\"1\",\"column\":\"1\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Title\",\"inputName\":\"track_title\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"Enter Track Title Here\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"14iibgynqv40000000000\",\"fieldName\":\"Track Content\",\"inputName\":\"track_content\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Track Settings\",\"row\":\"2\",\"column\":\"2\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "media_media-image",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Feature Image\",\"inputName\":\"image_url\",\"featured_image\":\"\",\"defaultImage\":\"\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "media_media-audio",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"media_media-audio\",\"media_media-audio_cell\":\"1\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Featured Audio\",\"inputName\":\"audio_url\",\"featured_audio\":\"\",\"audio_url\":\"\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_select",
    "field_id": 7,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"2\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Track Status\",\"inputName\":\"track_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_date",
    "field_id": 8,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"2\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"1\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackgenreradio",
    "field_id": 9,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackgenreradio\",\"track_trackgenreradio_cell\":\"3\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Genre\",\"inputName\":\"fk_genre_id\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_tracklicenseselect",
    "field_id": 10,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_tracklicenseselect\",\"track_tracklicenseselect_cell\":\"3\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"License\",\"inputName\":\"fk_license_id\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "input_text",
    "field_id": 11,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"4\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Track Slug\",\"inputName\":\"track_slug\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"Track Slug (Optional)\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "Track Page",
    "field_name": "track_trackartistselect",
    "field_id": 12,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"track_trackartistselect\",\"track_trackartistselect_cell\":\"4\",\"field_slug_unique_hash\":\"7e5llf54gds0000000000\",\"fieldName\":\"Artist\",\"inputName\":\"fk_artist_id\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
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
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4yl3ix1a5280000000000\",\"fieldName\":\"SEO Settings\",\"row\":\"1\",\"column\":\"1\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"rjs1x3ha48g000000000\",\"fieldName\":\"Title (Optional)\",\"inputName\":\"seo_title\",\"textType\":\"text\",\"maxChar\":\"100\",\"placeholder\":\"Auto-generate from title if empty\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "SEO Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6n1be5c9ua40000000000\",\"fieldName\":\"Description (Optional)\",\"inputName\":\"seo_description\",\"textType\":\"textarea\",\"maxChar\":\"250\",\"placeholder\":\"Enter SEO Description\",\"readOnly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\",\"elementWrapper\":\"\",\"classes\":\"\",\"ids\":\"\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_fieldselection",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"5rjc3bz2muc0000000000\",\"fieldName\":\"Site Header\",\"inputName\":\"site_header\",\"fieldSlug\":\"site-header:6\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4gb5s01t2680000000000\",\"fieldName\":\"Post Main Element\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"main\",\"attributes\":\"id=\\\"main\\\" class=\\\"flex:one bg:gray-one\\\" tabindex=\\\"-1\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4gb5s01t2680000000000\",\"fieldName\":\"Post Header Element\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"header\",\"attributes\":\"class=\\\"main-header text-align:center padding:default\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4gb5s01t2680000000000\",\"fieldName\":\"Post Header Text\",\"inputName\":\"post_header_text\",\"textType\":\"text\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"defaultValue\":\"Blog Posts\",\"elementWrapper\":\"\",\"attributes\":\"\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4gb5s01t2680000000000\",\"fieldName\":\"Div\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"div\",\"attributes\":\"class=\\\"search-admin width:100% margin-top:0\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4gb5s01t2680000000000\",\"fieldName\":\"Form\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"form\",\"attributes\":\"action=\\\"\\\" class=\\\"width:100% d:flex justify-content:center\\\" method=\\\"GET\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4gb5s01t2680000000000\",\"fieldName\":\"Form Input\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"input\",\"attributes\":\"style = \\\"width: clamp(40%, (800px - 100vw) * 1000, 70%);\\\" type=\\\"search\\\" required=\\\"\\\" class=\\\"border-radius:40px\\\" name=\\\"query\\\" aria-label=\\\"Search and Hit Enter\\\" placeholder=\\\"Search &amp; Hit Enter\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_rowcolumn",
    "field_id": 8,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"4gb5s01t2680000000000\",\"fieldName\":\"Posts Container\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"elementName\":\"ul\",\"attributes\":\"id =\\\"postloop\\\" class=\\\"admin-widget list:style:none d:flex flex-wrap:wrap flex-gap padding:default\\\"\",\"handleViewProcessing\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "post_posts",
    "field_id": 9,
    "field_parent_id": 8,
    "field_options": "{\"field_slug\":\"post_posts\",\"post_posts_cell\":\"1\",\"field_slug_unique_hash\":\"4gb5s01t2680000000000\",\"fieldName\":\"Posts Loop\",\"showPostImage\":\"0\",\"noOfPostPerPage\":\"10\",\"postDescriptionLength\":\"250\",\"postInCategories\":\"\",\"readMoreLabel\":\"Read More\",\"elementWrapper\":\"li\",\"attributes\":\"tabindex=\\\"0\\\" class=\\\"admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer position:relative justify-content:flex-start\\\" data-selected=\\\"false\\\"\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_tonicstemplatesystem",
    "field_id": 10,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_tonicstemplatesystem\",\"field_slug_unique_hash\":\"3jmpdpiyuii0000000000\",\"fieldName\":\"Post Prev and Next Button\",\"tonicsTemplateFrag\":\"[[import(\\\"Modules::Core/Views/Blocks/Default\\\")]]\\n\\n<div class=\\\"d:flex flex-gap:small justify-content:space-evenly padding:default\\\">\\n\\n    [[if(\\\"v[PostLoopData.prev_page_url]\\\")\\n<a type=\\\"submit\\\" class=\\\"border:none color:black border-width:default border:black padding:default\\n                        margin-top:0 cart-width cursor:pointer max-width:200 text-align:center text-underline\\\"\\n   title=\\\"Prev\\\" href=\\\"[[_v('PostLoopData.prev_page_url')]]#postloop\\\">\\n    Prev\\n</a>\\n    ]]\\n\\n    [[if(\\\"v[PostLoopData.next_page_url]\\\")\\n<a type=\\\"submit\\\" class=\\\"border:none color:black border-width:default border:black padding:default\\n                        margin-top:0 cart-width cursor:pointer max-width:200 text-align:center text-underline\\\"\\n   title=\\\"Next\\\" href=\\\"[[_v('PostLoopData.next_page_url')]]#postloop\\\">\\n    Next\\n</a>\\n    ]]\\n\\n</div>\",\"handleViewProcessing\":\"1\"}"
  },
  {
    "fk_field_id": "User Post Page",
    "field_name": "modular_fieldselection",
    "field_id": 11,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"7bgkbjdeuq80000000000\",\"fieldName\":\"Site Footer\",\"inputName\":\"site_footer\",\"fieldSlug\":\"site-footer:7\",\"handleViewProcessing\":\"1\"}"
  }
]
JSON;
        return json_decode($json);
    }
}