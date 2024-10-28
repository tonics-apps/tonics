<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Apps\TonicsSeo;

use App\Apps\TonicsSeo\EventHandler\HandleOldSeoURLForSEORedirection;
use App\Apps\TonicsSeo\EventHandler\TonicsSeoFieldSelectionHandler;
use App\Apps\TonicsSeo\EventHandler\TonicsStructuredDataFAQHandlerAndSelection;
use App\Apps\TonicsSeo\EventHandler\ViewHookIntoHandler;
use App\Apps\TonicsSeo\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\FieldSelectionDropper\OnAddFieldSelectionDropperEvent;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnAfterPreSavePostEditorFieldItems;
use App\Modules\Field\Events\OnEditorFieldSelection;
use Devsrealm\TonicsRouterSystem\Route;

class TonicsSeoActivator implements ExtensionConfig, FieldItemsExtensionConfig
{
    use Routes;

    private FieldData $fieldData;

    public function __construct ()
    {
        $this->fieldData = new FieldData();
    }

    /**
     * @inheritDoc
     */
    public function enabled (): bool
    {
        return true;
    }

    /**
     * @throws \ReflectionException
     */
    public function route (Route $routes): Route
    {
        $route = $this->routeApi($routes);
        return $this->routeWeb($route);
    }

    /**
     * @inheritDoc
     */
    public function events (): array
    {
        return [
            OnHookIntoTemplate::class => [
                ViewHookIntoHandler::class,
            ],

            OnAfterPreSavePostEditorFieldItems::class => [
                HandleOldSeoURLForSEORedirection::class,
            ],

            OnEditorFieldSelection::class => [
                TonicsStructuredDataFAQHandlerAndSelection::class,
            ],

            FieldTemplateFile::class => [
                TonicsStructuredDataFAQHandlerAndSelection::class,
            ],

            OnAddFieldSelectionDropperEvent::class => [
                TonicsSeoFieldSelectionHandler::class,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function tables (): array
    {
        return [];
    }

    /**
     * @throws \Exception
     */
    public function onInstall (): void
    {
        $this->fieldData->importFieldItems($this->fieldItems());
    }

    public function onUninstall (): void
    {
        return;
    }

    /**
     * @throws \Exception
     */
    public function onUpdate (): void
    {
        $this->fieldData->importFieldItems($this->fieldItems());
    }

    /**
     * @throws \Exception
     */
    public function onDelete (): void
    {
        // should not be deleted as it is a dangerous operation that can destroy users seo
        /*db(onGetDB: function (TonicsQuery $db) {
            $toDelete = ['app-tonicsseo-settings'];
            $tb = $this->fieldData->getFieldTable();
            $db->FastDelete($tb, db()->WhereIn(table()->getColumn($tb, 'field_slug'), $toDelete));
        });*/
    }

    /**
     * @throws \Exception
     */
    public function info (): array
    {
        return [
            "name"                 => "TonicsSeo",
            "type"                 => "Tool", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            "slug_id"              => "a3d355df-276e-11ef-9736-124c30cfdb6b",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-app.1718095500',
            "description"          => "This is TonicsSeo",
            "info_url"             => '',
            "settings_page"        => route('tonicsSeo.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/app-tonics_seo/releases/latest",
            "authors"              => [
                "name"  => "Your Name",
                "email" => "name@website.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }

    function fieldItems (): array
    {
        $json = <<<'JSON'
[
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"79nwarxrgus000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_structured_data_container\",\"fieldName\":\"Product Review Structured Data\",\"inputName\":\"app_tonics_seo_structured_data_product_review_structured_data_container\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"152hpn7fxscg00000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_name\",\"fieldName\":\"Name (Optional)\",\"inputName\":\"app_tonics_seo_structured_data_product_review_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Product Name or Default To Title\",\"readOnly\":\"0\",\"required\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4uc4nnlzc8o0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_desc\",\"fieldName\":\"Description\",\"inputName\":\"app_tonics_seo_structured_data_product_review_desc\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Product Description\",\"readOnly\":\"0\",\"required\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"30tyn59n2ho0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_positiveNoteContainer\",\"fieldName\":\"Positive Notes\",\"inputName\":\"app_tonics_seo_structured_data_product_review_positiveNoteContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Positive Note\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "input_text",
		"field_id": 5,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 4,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6el20oge5040000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_positiveNote\",\"fieldName\":\"Note\",\"inputName\":\"app_tonics_seo_structured_data_product_review_positiveNote\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Positive Note\",\"readOnly\":\"0\",\"required\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 6,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"52sbsagy8s00000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_negativeNoteContainer\",\"fieldName\":\"Negative Notes\",\"inputName\":\"app_tonics_seo_structured_data_product_review_negativeNoteContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Negative Note\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 6,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2tna4lh34yg000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_negativeNote\",\"fieldName\":\"Note\",\"inputName\":\"app_tonics_seo_structured_data_product_review_negativeNote\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Negative Note\",\"readOnly\":\"0\",\"required\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "post_postauthorselect",
		"field_id": 8,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"post_postauthorselect\",\"post_postauthorselect_cell\":\"2\",\"field_slug_unique_hash\":\"5djg9cp07x00000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_author\",\"fieldName\":\"Author\",\"inputName\":\"app_tonics_seo_structured_data_product_review_author\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "modular_rowcolumn",
		"field_id": 9,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"3xvl4l2gppo0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_aggregrateRatingContainer\",\"fieldName\":\"Aggregate Rating\",\"inputName\":\"app_tonics_seo_structured_data_product_review_aggregateRatingContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "input_range",
		"field_id": 10,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 9,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_range\",\"input_range_cell\":\"1\",\"field_slug_unique_hash\":\"560co9p18w40000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_rating_value\",\"fieldName\":\"Rating Value\",\"inputName\":\"app_tonics_seo_structured_data_product_review_rating_value\",\"min\":\"0\",\"max\":\"5\",\"step\":\"0.1\",\"defaultValue\":\"4\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review] [Old Version] [Deprecated]",
		"field_name": "input_text",
		"field_id": 11,
		"field_slug": "app-tonicsseo-structured-data-product-review",
		"field_parent_id": 9,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"28uxrbdg0ljw00000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_review_count\",\"fieldName\":\"Review Count\",\"inputName\":\"app_tonics_seo_structured_data_product_review_review_count\",\"textType\":\"number\",\"defaultValue\":\"1\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"2s1x14s3oq20000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"TonicsSeo Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5zkdnvjtnf40000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Homepage Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"375vk7a1jy00000000000\",\"field_input_name\":\"app_tonicsseo_site_title\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Site Title\",\"inputName\":\"app_tonicsseo_site_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"My Site Title\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_select",
		"field_id": 4,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3iwf6dbtdhu0000000000\",\"field_input_name\":\"app_tonicsseo_site_title_location\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Title Location\",\"inputName\":\"app_tonicsseo_site_title_location\",\"selectData\":\"left:Left,right:Right\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_select",
		"field_id": 5,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"67p58l03wa40000000000\",\"field_input_name\":\"app_tonicsseo_site_title_separator\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Separator\",\"inputName\":\"app_tonicsseo_site_title_separator\",\"selectData\":\"-,|,•,<,>,/,«,»\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "media_media-image",
		"field_id": 6,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"2lfcxgeetxk0000000000\",\"field_input_name\":\"app_tonicsseo_site_favicon\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Favicon (Recommend SVG Image)\",\"inputName\":\"app_tonicsseo_site_favicon\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 7,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"2n2dir4cqbs0000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Sitemap Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "tool_sitemap",
		"field_id": 8,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 7,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"tool_sitemap\",\"tool_sitemap_cell\":\"1\",\"field_slug_unique_hash\":\"6j1hu1kvo0k0000000000\",\"field_input_name\":\"app_tonicsseo_sitemap_handlers\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Choose Sitemap To Include:\",\"inputName\":\"app_tonicsseo_sitemap_handlers\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 7,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"68lon3vafcw000000000\",\"field_input_name\":\"app_tonicsseo_sitemap_per_page\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Sitemap Per Page\",\"inputName\":\"app_tonicsseo_sitemap_per_page\",\"textType\":\"number\",\"defaultValue\":\"1000\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Sitemap to query per page\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_choices",
		"field_id": 10,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 7,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"input_choices\",\"input_choices_cell\":\"1\",\"field_slug_unique_hash\":\"5693n8v6vos0000000000\",\"field_input_name\":\"app_tonicsseo_notify_search_engine\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Notify Search Engines\",\"inputName\":\"app_tonicsseo_notify_search_engine\",\"choiceType\":\"checkbox\",\"choices\":\"google:Notify Google?,bing:Notify Bing?\",\"defaultValue\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_select",
		"field_id": 11,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 7,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"6rwz58tt4ls0000000000\",\"field_input_name\":\"app_tonicsseo_ping_search_engine\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Ping Search Engines Every:\",\"inputName\":\"app_tonicsseo_ping_search_engine\",\"selectData\":\"5min:5 Minutes,15min:15 Minutes,30min:30 Minutes,45min:45 Minutes,1hr:1 Hour,6hr:6 Hour,12hr:12 Hour,24hr:24 Hour\",\"defaultValue\":\"30min\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 12,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"70xms7v99180000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Webmaster Integration Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_text",
		"field_id": 13,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 12,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"rxxr4uejo34000000000\",\"field_input_name\":\"app_tonicsseo_google_verification_code\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Google Search Console Verification Code\",\"inputName\":\"app_tonicsseo_google_verification_code\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"xxxxxxxx\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_text",
		"field_id": 14,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 12,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3x91qw5vecc0000000000\",\"field_input_name\":\"app_tonicsseo_bing_verification_code\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Bing Webmaster Verification Code\",\"inputName\":\"app_tonicsseo_bing_verification_code\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"xxxxxxxx\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_text",
		"field_id": 15,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 12,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"65vx1eki3q00000000000\",\"field_input_name\":\"app_tonicsseo_pinterest_verification_code\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Pinterest Analytics Verification Code\",\"inputName\":\"app_tonicsseo_pinterest_verification_code\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"xxxxxxxx\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_text",
		"field_id": 16,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"mof2x91o60w000000000\",\"field_input_name\":\"app_tonicsseo_robots_txt\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"robots.txt\",\"inputName\":\"app_tonicsseo_robots_txt\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter robots directive\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "modular_rowcolumn",
		"field_id": 17,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"3pb63vsp7z2000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Injection\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_select",
		"field_id": 18,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 17,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1dffum1vovi8000000000\",\"field_input_name\":\"app_tonicsseo_disable_injection_logged_in\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Disable Injection When Logged In\",\"inputName\":\"app_tonicsseo_disable_injection_logged_in\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_text",
		"field_id": 19,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 17,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5kfqpi7vhe8000000000\",\"field_input_name\":\"app_tonicsseo_in_head\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"In Head\",\"inputName\":\"app_tonicsseo_in_head\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Settings",
		"field_name": "input_text",
		"field_id": 20,
		"field_slug": "app-tonicsseo-settings",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"43ql29nk48q0000000000\",\"field_input_name\":\"app_tonicsseo_ads_txt\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"ads.txt\",\"inputName\":\"app_tonicsseo_ads_txt\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Add network directive\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article] [Old Version] [Deprecated]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicsseo-structured-data-article",
		"field_parent_id": null,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"15vv00nocbhc000000000\",\"field_input_name\":\"\",\"fieldName\":\"Article Structured Data\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"1.5fr 1.3fr\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article] [Old Version] [Deprecated]",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "app-tonicsseo-structured-data-article",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2o4yw5rkov40000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_article_type\",\"fieldName\":\"Article Type\",\"inputName\":\"app_tonics_seo_structured_data_article_article_type\",\"selectData\":\"Article, NewsArticle, BlogPosting\",\"defaultValue\":\"Article\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article] [Old Version] [Deprecated]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicsseo-structured-data-article",
		"field_parent_id": 1,
		"field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1yr2bdd8lycg000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_headline\",\"fieldName\":\"Headline (Optional)\",\"inputName\":\"app_tonics_seo_structured_data_article_headline\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"110\",\"placeholder\":\"Enter Headline (Optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article] [Old Version] [Deprecated]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "app-tonicsseo-structured-data-article",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"2\",\"field_slug_unique_hash\":\"48skwmelb4i0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_image_repeater\",\"fieldName\":\"Article Image(s) (Optional)\",\"inputName\":\"app_tonics_seo_structured_data_article_image_repeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Image\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article] [Old Version] [Deprecated]",
		"field_name": "media_media-image",
		"field_id": 5,
		"field_slug": "app-tonicsseo-structured-data-article",
		"field_parent_id": 4,
		"field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"4avs56h88ta0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_image\",\"fieldName\":\"Image\",\"inputName\":\"app_tonics_seo_structured_data_article_image\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article] [Old Version] [Deprecated]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 6,
		"field_slug": "app-tonicsseo-structured-data-article",
		"field_parent_id": 1,
		"field_options": "{\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"2\",\"field_slug_unique_hash\":\"2f9ckug6olj4000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_author_repeater\",\"fieldName\":\"Article Author(s) (Optional)\",\"inputName\":\"app_tonics_seo_structured_data_article_author_repeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Author\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article] [Old Version] [Deprecated]",
		"field_name": "post_postauthorselect",
		"field_id": 7,
		"field_slug": "app-tonicsseo-structured-data-article",
		"field_parent_id": 6,
		"field_options": "{\"field_slug\":\"post_postauthorselect\",\"post_postauthorselect_cell\":\"1\",\"field_slug_unique_hash\":\"2ipise1kihk0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_author\",\"fieldName\":\"Author Select\",\"inputName\":\"app_tonics_seo_structured_data_article_author\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [FAQ]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 1,
		"field_slug": "app-tonicsseo-structured-data-faq",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"3k5hdxjzsy60000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"FAQ Page\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"1\",\"repeat_button_text\":\"Repeat F.A.Q\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [FAQ]",
		"field_name": "input_text",
		"field_id": 2,
		"field_slug": "app-tonicsseo-structured-data-faq",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2825v3vfs64g000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_faq_question\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Question\",\"inputName\":\"app_tonics_seo_structured_data_faq_question\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter F.A.Q Question\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [FAQ]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicsseo-structured-data-faq",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"4ni8urz2vau0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_faq_answer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Answer\",\"inputName\":\"app_tonics_seo_structured_data_faq_answer\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter F.A.Q Answer\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6yxhxylla2w0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_structured_data_container\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Product Review Structured Data\",\"inputName\":\"app_tonics_seo_structured_data_product_review_structured_data_container\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "modular_rowcolumn",
		"field_id": 2,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1vxs5m65jcrk00000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Basic Info\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"152hpn7fxscg00000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_name\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Name (Optional)\",\"inputName\":\"app_tonics_seo_structured_data_product_review_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Product Name or Default To Title\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "input_text",
		"field_id": 4,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 2,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4uc4nnlzc8o0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_desc\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Description\",\"inputName\":\"app_tonics_seo_structured_data_product_review_desc\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Product Description\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "modular_rowcolumn",
		"field_id": 5,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"13wrmdxxndb4000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Notes\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 6,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"30tyn59n2ho0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_positiveNoteContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Positive Notes\",\"inputName\":\"app_tonics_seo_structured_data_product_review_positiveNoteContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"0\",\"repeat_button_text\":\"Repeat Positive Note\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "input_text",
		"field_id": 7,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6el20oge5040000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_positiveNote\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Note\",\"inputName\":\"app_tonics_seo_structured_data_product_review_positiveNote\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Positive Note\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 8,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 5,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"52sbsagy8s00000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_negativeNoteContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Negative Notes\",\"inputName\":\"app_tonics_seo_structured_data_product_review_negativeNoteContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"0\",\"repeat_button_text\":\"Repeat Negative Note\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "input_text",
		"field_id": 9,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 8,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2tna4lh34yg000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_negativeNote\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Note\",\"inputName\":\"app_tonics_seo_structured_data_product_review_negativeNote\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Negative Note\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "modular_rowcolumn",
		"field_id": 10,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6p39untzzo00000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Others\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "interface_table",
		"field_id": 11,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 10,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"interface_table\",\"interface_table_cell\":\"1\",\"field_slug_unique_hash\":\"3k2zlwpbmw60000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_author\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Author\",\"inputName\":\"app_tonics_seo_structured_data_product_review_author\",\"tableName\":\"user\",\"orderBy\":\"user_id\",\"colNameDisplay\":\"user_name\",\"colNameValue\":\"user_name\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "modular_rowcolumn",
		"field_id": 12,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 10,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"3xvl4l2gppo0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_aggregateRatingContainer\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Aggregate Rating\",\"inputName\":\"app_tonics_seo_structured_data_product_review_aggregateRatingContainer\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"0\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "input_range",
		"field_id": 13,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 12,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_range\",\"input_range_cell\":\"1\",\"field_slug_unique_hash\":\"560co9p18w40000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_rating_value\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Rating Value\",\"inputName\":\"app_tonics_seo_structured_data_product_review_rating_value\",\"min\":\"0\",\"max\":\"5\",\"step\":\"0.1\",\"defaultValue\":\"4\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Product Review]",
		"field_name": "input_text",
		"field_id": 14,
		"field_slug": "app-tonicsseo-structured-data-product-review-new",
		"field_parent_id": 12,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"28uxrbdg0ljw00000000\",\"field_input_name\":\"app_tonics_seo_structured_data_product_review_review_count\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Review Count\",\"inputName\":\"app_tonics_seo_structured_data_product_review_review_count\",\"textType\":\"number\",\"defaultValue\":\"1\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"\",\"maxChar\":\"\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article]",
		"field_name": "modular_rowcolumn",
		"field_id": 1,
		"field_slug": "app-tonicsseo-structured-data-article-new",
		"field_parent_id": null,
		"field_options": "{\"toggle_state\":true,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5o1l0ivohd00000000000\",\"field_input_name\":\"\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Article Structured Data\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"1\",\"styles\":\"\",\"toggleable\":\"\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article]",
		"field_name": "input_select",
		"field_id": 2,
		"field_slug": "app-tonicsseo-structured-data-article-new",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2j0wha9pqtw0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_article_type\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Article Type\",\"inputName\":\"app_tonics_seo_structured_data_article_article_type\",\"selectData\":\"Article,NewsArticle,BlogPosting\",\"defaultValue\":\"Article\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article]",
		"field_name": "input_text",
		"field_id": 3,
		"field_slug": "app-tonicsseo-structured-data-article-new",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1yr2bdd8lycg000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_headline\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Headline (Optional)\",\"inputName\":\"app_tonics_seo_structured_data_article_headline\",\"textType\":\"text\",\"defaultValue\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"placeholder\":\"Enter Headline (Optional)\",\"maxChar\":\"110\",\"readOnly\":\"0\",\"required\":\"0\",\"styles\":\"\",\"toggleable\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 4,
		"field_slug": "app-tonicsseo-structured-data-article-new",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"1fbeuob3uxpc000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_image_repeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Article Image(s) (Optional)\",\"inputName\":\"app_tonics_seo_structured_data_article_image_repeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"0\",\"repeat_button_text\":\"Repeat Image\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article]",
		"field_name": "media_media-image",
		"field_id": 5,
		"field_slug": "app-tonicsseo-structured-data-article-new",
		"field_parent_id": 4,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"2gwzvi93v0k0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_image_repeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Image\",\"inputName\":\"app_tonics_seo_structured_data_article_image\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article]",
		"field_name": "modular_rowcolumnrepeater",
		"field_id": 6,
		"field_slug": "app-tonicsseo-structured-data-article-new",
		"field_parent_id": 1,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"modular_rowcolumnrepeater\",\"modular_rowcolumnrepeater_cell\":\"1\",\"field_slug_unique_hash\":\"6w72p27ujc80000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_author_repeater\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Article Author(s) (Optional)\",\"inputName\":\"app_tonics_seo_structured_data_article_author_repeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"useTab\":\"0\",\"toggleable\":\"0\",\"repeat_button_text\":\"Repeat Author\",\"cell\":\"on\"}"
	},
	{
		"field_field_name": "App TonicsSeo Structured Data »» [Article]",
		"field_name": "interface_table",
		"field_id": 7,
		"field_slug": "app-tonicsseo-structured-data-article-new",
		"field_parent_id": 6,
		"field_options": "{\"toggle_state\":false,\"field_slug\":\"interface_table\",\"interface_table_cell\":\"1\",\"field_slug_unique_hash\":\"571ctmvi4jk0000000000\",\"field_input_name\":\"app_tonics_seo_structured_data_article_author\",\"hook_name\":\"\",\"tabbed_key\":\"\",\"fieldName\":\"Author\",\"inputName\":\"app_tonics_seo_structured_data_article_author\",\"tableName\":\"user\",\"orderBy\":\"user_id\",\"colNameDisplay\":\"user_name\",\"colNameValue\":\"user_name\",\"info\":\"\",\"hideInUserEditForm\":\"0\",\"multiSelect\":\"0\"}"
	}
]
JSON;
        return json_decode($json);
    }
}