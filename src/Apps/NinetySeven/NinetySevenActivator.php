<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven;

use App\Apps\NinetySeven\EventHandler\EditorsAssetsHandler;
use App\Apps\NinetySeven\EventHandler\ConfigureNinetySevenPageSettings;
use App\Apps\NinetySeven\EventHandler\PageTemplates\TonicsNinetySevenHomePageTemplate;
use App\Apps\NinetySeven\EventHandler\PageTemplates\TonicsNinetySevenPostPageTemplate;
use App\Apps\NinetySeven\Route\Routes;
use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Library\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\Events\EditorsAsset;
use App\Modules\Field\Data\FieldData;
use App\Modules\Page\Events\BeforePageView;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsRouterSystem\Route;

class NinetySevenActivator implements ExtensionConfig, FieldItemsExtensionConfig
{
    use Routes;

    private FieldData $fieldData;

    public function __construct(){
        $this->fieldData = new FieldData();
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [
            EditorsAsset::class => [
                EditorsAssetsHandler::class
            ],

            BeforePageView::class => [
                ConfigureNinetySevenPageSettings::class
            ],

            OnPageTemplate::class => [
                TonicsNinetySevenHomePageTemplate::class,
                TonicsNinetySevenPostPageTemplate::class
            ]
        ];
    }

    /**
     * @inheritDoc
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        return $this->routeWeb($routes);
    }

    /**
     * @inheritDoc
     */
    public function tables(): array
    {
        return [];
    }

    /**
     * @throws \Exception
     */
    public function onInstall(): void
    {
        $this->fieldData->importFieldItems($this->fieldItems());
    }

    /**
     * @throws \Exception
     */
    public function onUninstall(): void
    {
    }

    /**
     * @throws \Exception
     */
    public function info(): array
    {
        return [
            "name" => "NinetySeven",
            "type" => "Theme",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1670074612',
            "description" => "NinetySeven Theme, The First Tonic Theme",
            "info_url" => '',
            "settings_page" => route('ninetySeven.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/theme-ninetyseven/releases/latest",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }

    public function onUpdate(): void
    {
        // TODO: Implement onUpdate() method.
    }

    public function fieldItems(): array
    {
        $json =<<<'JSON'
[
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4xekzw3z6ew0000000000\",\"field_input_name\":\"ninetySeven_featured_and_top_best_modular\",\"fieldName\":\"Featured and Top Best\",\"inputName\":\"ninetySeven_featured_and_top_best_modular\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"1fr\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6wgc8a4t6ns0000000000\",\"field_input_name\":\"ninetySeven_featured_label\",\"fieldName\":\"Featured  Label\",\"inputName\":\"ninetySeven_featured_label\",\"textType\":\"text\",\"defaultValue\":\"Featured\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "modular_fieldselection",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"50p241i02fg0000000000\",\"field_input_name\":\"ninetySeven_featured_postQuery\",\"fieldName\":\"Featured Post Query\",\"inputName\":\"ninetySeven_featured_postQuery\",\"fieldSlug\":\"post-query-builder\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"20wx6ymlpnr4000000000\",\"field_input_name\":\"ninetySeven_best_label\",\"fieldName\":\"Best Label\",\"inputName\":\"ninetySeven_best_label\",\"textType\":\"text\",\"defaultValue\":\"Top Best\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "modular_fieldselection",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"2\",\"field_slug_unique_hash\":\"743gv1qsz740000000000\",\"field_input_name\":\"ninetySeven_best_postQuery\",\"fieldName\":\"Best Post Query\",\"inputName\":\"ninetySeven_best_postQuery\",\"fieldSlug\":\"post-query-builder\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "modular_rowcolumnrepeater",
    "field_id": 6,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[\"url\"],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"15e73bc1m9i8000000000\",\"field_input_name\":\"ninetySeven_other_post_category_repeater\",\"fieldName\":\"Other Post Category\",\"inputName\":\"ninetySeven_other_post_category_repeater\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Repeat Post Category\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "input_text",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3py5fz8te140000000000\",\"field_input_name\":\"ninetySeven_category_title\",\"fieldName\":\"Category Title\",\"inputName\":\"ninetySeven_category_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Category Title\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "input_text",
    "field_id": 8,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[\"url\"],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"28m1lcaob24g000000000\",\"field_input_name\":\"ninetySeven_category_link\",\"fieldName\":\"Category Link\",\"inputName\":\"ninetySeven_category_link\",\"textType\":\"url\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Category Link\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "input_text",
    "field_id": 9,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"136tqlcfowdc00000000\",\"field_input_name\":\"ninetySeven_category_description\",\"fieldName\":\"Category Description\",\"inputName\":\"ninetySeven_category_description\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Category Description\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "modular_fieldselection",
    "field_id": 10,
    "field_parent_id": 6,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"modular_fieldselection_cell\":\"1\",\"field_slug_unique_hash\":\"1b4e4dbm2aow000000000\",\"field_input_name\":\"ninetySeven_categoryQuery\",\"fieldName\":\"Category Query\",\"inputName\":\"ninetySeven_categoryQuery\",\"fieldSlug\":\"post-query-builder\",\"hideInUserEditForm\":\"0\",\"expandField\":\"1\"}"
  },
  {
    "fk_field_id": "App Ninety Seven Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"202kaq8fyz5s000000000\",\"field_input_name\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App Ninety Seven Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"35iwumwke7e000000000\",\"field_input_name\":\"\",\"fieldName\":\"Logo and Navigation\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"grid_template_col\":\"2fr 3fr\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App Ninety Seven Settings",
    "field_name": "media_media-image",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"4v3issqqxls0000000000\",\"field_input_name\":\"site_logo\",\"fieldName\":\"site_logo\",\"inputName\":\"site_logo\",\"imageLink\":\"/logo/o-ola-micky-logo.svg\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
  },
  {
    "fk_field_id": "App Ninety Seven Settings",
    "field_name": "menu_menus",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_slug\":\"menu_menus\",\"menu_menus_cell\":\"2\",\"field_slug_unique_hash\":\"7ozvcwh4f4w000000000\",\"field_input_name\":\"site_header_menu\",\"fieldName\":\"site_header_menu\",\"inputName\":\"site_header_menu\",\"menuSlug\":\"header-menu\",\"displayName\":\"1\"}"
  },
  {
    "fk_field_id": "App Ninety Seven Settings",
    "field_name": "widget_widgets",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"widget_widgets\",\"widget_widgets_cell\":\"2\",\"field_slug_unique_hash\":\"6kcp1ee1xu00000000000\",\"field_input_name\":\"sidebar_widget\",\"fieldName\":\"Sidebar Widget\",\"inputName\":\"sidebar_widget\",\"widgetSlug\":\"sidebar-widget\"}"
  },
  {
    "fk_field_id": "App Ninety Seven Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 6,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"3\",\"field_slug_unique_hash\":\"5xhyxqw7s1g0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Site Footer\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"1\",\"grid_template_col\":\"1\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App Ninety Seven Settings",
    "field_name": "menu_menus",
    "field_id": 7,
    "field_parent_id": 6,
    "field_options": "{\"field_slug\":\"menu_menus\",\"menu_menus_cell\":\"1\",\"field_slug_unique_hash\":\"6mkrfyi63z00000000000\",\"field_input_name\":\"site_footer_menu\",\"fieldName\":\"site_footer\",\"inputName\":\"site_footer_menu\",\"menuSlug\":\"footer-menu\",\"displayName\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven Settings",
    "field_name": "input_rich-text",
    "field_id": 8,
    "field_parent_id": 6,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"2\",\"field_slug_unique_hash\":\"6h1171hdkzk0000000000\",\"field_input_name\":\"site_credit\",\"fieldName\":\"site_credit\",\"inputName\":\"site_credit\",\"defaultValue\":\"<span class=\\\"site-footer-info\\\"> \\u00a9 2022 Devsrealm |        <a href=\\\"https://devsrealm.com/\\\">Powered by Tonics</a> | Theme: <a href=\\\"#\\\">NinetySeven by DevsRealmGuy</a>    </span>\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\"}"
  }
]
JSON;
        return json_decode($json);
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData(FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function onDelete(): void
    {
        $toDelete = ['app-ninety-seven-settings', 'app-ninety-seven-post-home-page'];
        $tb = $this->fieldData->getFieldTable();
        db()->FastDelete($tb, db()->WhereIn(table()->getColumn($tb, 'field_slug'), $toDelete));
    }
}