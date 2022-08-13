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
use App\Apps\NinetySeven\Route\Routes;
use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\EditorsAsset;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsRouterSystem\Route;

class NinetySevenActivator implements ExtensionConfig
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
        $this->fieldData->importFieldItems($this->getFieldItemsToImport());
    }

    /**
     * @throws \Exception
     */
    public function onUninstall(): void
    {
    }

    public function info(): array
    {
        return [
            "name" => "NinetySeven",
            "type" => "Theme",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1656111209',
            "description" => "NinetySeven Theme, The First Tonic Theme",
            "info_url" => '',
            "settings_page" => null, // can be null or a route name
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

    public function getFieldItemsToImport(): array
    {
        $json =<<<'JSON'
[
  {
    "fk_field_id": "App Ninety Seven [Site Header]",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4v4s1n51adq000000000\",\"fieldName\":\"site_logo_site_nav\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"2\",\"hideInUserEditForm\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Site Header]",
    "field_name": "media_media-image",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"5cnkgz4g8p00000000000\",\"fieldName\":\"site_logo\",\"inputName\":\"site_logo\",\"imageLink\":\"/logo/o-ola-micky-logo.svg\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Site Header]",
    "field_name": "menu_menus",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"menu_menus\",\"menu_menus_cell\":\"2\",\"field_slug_unique_hash\":\"2aw1emycj6dc000000000\",\"fieldName\":\"site_header_menu\",\"inputName\":\"site_header_menu\",\"menuSlug\":\"header-menu:1\",\"displayName\":\"1\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Site Header]",
    "field_name": "modular_fieldselection",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"6lpd5efqzfs0000000000\",\"fieldName\":\"seo_settings\",\"inputName\":\"\",\"fieldSlug\":\"seo-settings:41\",\"hideInUserEditForm\":\"1\",\"expandField\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Site Footer]",
    "field_name": "menu_menus",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"menu_menus\",\"field_slug_unique_hash\":\"5v7m7yxl4gw0000000000\",\"fieldName\":\"site_footer_menu\",\"inputName\":\"site_footer_menu\",\"menuSlug\":\"footer-menu:2\",\"displayName\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "modular_fieldselection",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"51dprc5m6tk0000000000\",\"fieldName\":\"site_header\",\"inputName\":\"site_header\",\"fieldSlug\":\"app-ninetyseven-site-header:43\",\"hideInUserEditForm\":\"1\",\"expandField\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "modular_fieldselection",
    "field_id": 2,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"55vt8iojvqo0000000000\",\"fieldName\":\"site_footer\",\"inputName\":\"site_footer\",\"fieldSlug\":\"app-ninetyseven-site-footer:44\",\"hideInUserEditForm\":\"1\",\"expandField\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 3,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6w7rn2aufls0000000000\",\"fieldName\":\"Page Settings\",\"inputName\":\"page_settings\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2f0ralb0iqm8000000000\",\"fieldName\":\"header title\",\"inputName\":\"header_title_text\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"elementWrapper\":\"\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "input_text",
    "field_id": 5,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"23dntju9g5y8000000000\",\"fieldName\":\"header_description\",\"inputName\":\"header_description_text\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"elementWrapper\":\"\",\"attributes\":\"\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Home Page]",
    "field_name": "menu_menus",
    "field_id": 6,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"menu_menus\",\"field_slug_unique_hash\":\"4x55mpw6ejg0000000000\",\"fieldName\":\"filter post category\",\"inputName\":\"filter_post_category\",\"menuSlug\":\"post-categories:3\",\"displayName\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Post Category]",
    "field_name": "menu_menus",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"menu_menus\",\"field_slug_unique_hash\":\"4srcdjdiudg0000000000\",\"fieldName\":\"post menu category\",\"inputName\":\"\",\"menuSlug\":\"post-categories:3\",\"displayName\":\"1\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Single Post View]",
    "field_name": "modular_fieldselection",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"6xeusz9866c0000000000\",\"fieldName\":\"site_header\",\"inputName\":\"\",\"fieldSlug\":\"app-ninetyseven-site-header:43\",\"hideInUserEditForm\":\"1\",\"expandField\":\"0\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Single Post View]",
    "field_name": "widget_widgets",
    "field_id": 2,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"widget_widgets\",\"field_slug_unique_hash\":\"3qg02iumkbk0000000000\",\"fieldName\":\"sidebar widget\",\"inputName\":\"sidebar_widget\",\"widgetSlug\":\"sidebar-widget:1\"}"
  },
  {
    "fk_field_id": "App Ninety Seven [Single Post View]",
    "field_name": "modular_fieldselection",
    "field_id": 3,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldselection\",\"field_slug_unique_hash\":\"6uw3upsmjbc0000000000\",\"fieldName\":\"site_footer\",\"inputName\":\"\",\"fieldSlug\":\"app-ninetyseven-site-footer:44\",\"hideInUserEditForm\":\"1\",\"expandField\":\"0\"}"
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
        $toDelete = "'app-ninetyseven-site-header', 'app-ninetyseven-site-footer', 'app-ninetyseven-post-home-page', 'app-ninetyseven-post-category', 'app-ninetyseven-single-post-view'";
        db()->run("DELETE FROM {$this->fieldData->getFieldTable()} WHERE `field_slug` IN ($toDelete)");
    }
}