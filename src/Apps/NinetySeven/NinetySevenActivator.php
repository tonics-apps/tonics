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

use App\Apps\NinetySeven\EventHandler\AssetsHookHandler;
use App\Apps\NinetySeven\EventHandler\EditorsAssetsHandler;
use App\Apps\NinetySeven\EventHandler\ConfigureNinetySevenPageSettings;
use App\Apps\NinetySeven\EventHandler\Hook_AddSvgSymbols;
use App\Apps\NinetySeven\EventHandler\PageTemplates\AudioTonics\ThemeFolder\ThemeFolderViewHandler;
use App\Apps\NinetySeven\EventHandler\PageTemplates\AudioTonics\ThemeFolder\TonicsNinetySevenAudioTonicsThemeFolderHomeTemplate;
use App\Apps\NinetySeven\EventHandler\PageTemplates\AudioTonics\ThemeFolder\TonicsNinetySevenAudioTonicsThemeFolderTrackCategoryTemplate;
use App\Apps\NinetySeven\EventHandler\PageTemplates\AudioTonics\ThemeFolder\TonicsNinetySevenAudioTonicsThemeFolderTrackSingleTemplate;
use App\Apps\NinetySeven\EventHandler\PageTemplates\WriTonics\TonicsNinetySevenWriTonicsPostPageTemplate;
use App\Apps\NinetySeven\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\Events\EditorsAsset;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Field\Data\FieldData;
use App\Modules\Page\Events\BeforePageView;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
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
                TonicsNinetySevenWriTonicsPostPageTemplate::class,
                TonicsNinetySevenAudioTonicsThemeFolderHomeTemplate::class,
                TonicsNinetySevenAudioTonicsThemeFolderTrackCategoryTemplate::class,
                TonicsNinetySevenAudioTonicsThemeFolderTrackSingleTemplate::class,
            ],

            OnHookIntoTemplate::class => [
                AssetsHookHandler::class,
                ThemeFolderViewHandler::class,
                Hook_AddSvgSymbols::class
            ],
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
            "version" => '1-O-Ola.1677355966',
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
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"2\",\"field_slug_unique_hash\":\"6h1171hdkzk0000000000\",\"field_input_name\":\"site_credit\",\"fieldName\":\"site_credit\",\"inputName\":\"site_credit\",\"defaultValue\":\"<span class=\\\"site-footer-info\\\"> © 2023 Devsrealm |        <a href=\\\"https://tonics.app/\\\">Powered by Tonics</a> | Theme: <a href=\\\"#\\\">NinetySeven by DevsRealmGuy</a>    </span>\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\"}"
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
        db(onGetDB: function (TonicsQuery $db) use ($toDelete, $tb) {
            $newDB = db();
            $db->FastDelete($tb, $newDB->WhereIn(table()->getColumn($tb, 'field_slug'), $toDelete));
            $newDB->getTonicsQueryBuilder()->destroyPdoConnection();
        });

    }
}