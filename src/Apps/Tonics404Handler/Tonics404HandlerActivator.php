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

namespace App\Apps\Tonics404Handler;

use App\Apps\Tonics404Handler\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsRouterSystem\Route;

class Tonics404HandlerActivator implements ExtensionConfig, FieldItemsExtensionConfig
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
        return [];
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

    public function onUpdate (): void
    {
        return;
    }

    public function onDelete (): void
    {
        // TODO: Implement onDelete() method.
    }

    /**
     * @throws \Exception
     */
    public function info (): array
    {
        return [
            "name"                 => "Tonics404Handler",
            "type"                 => "Tool", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            "slug_id"              => '176525a1-276c-11ef-9736-124c30cfdb6b',
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-app.1718095500',
            "description"          => "This is Tonics404Handler",
            "info_url"             => '',
            "settings_page"        => route('tonics404Handler.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/app-tonics404_handler/releases/latest",
            "authors"              => [
                "name"  => "Your Name",
                "email" => "name@website.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }

    public function fieldItems (): array
    {
        $json = <<<'JSON'
[
  {
    "fk_field_id": "App Tonics404Handler Settings",
    "field_name": "modular_rowcolumnrepeater",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumnrepeater\",\"field_slug_unique_hash\":\"1hrbfmhv08u8000000000\",\"field_input_name\":\"tonics404handler_section\",\"fieldName\":\"Tonics404Handler Redirection\",\"inputName\":\"tonics404handler_section\",\"row\":\"1\",\"column\":\"3\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"disallowRepeat\":\"0\",\"repeat_button_text\":\"Duplicate Redirection Settings\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App Tonics404Handler Settings",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1mxhp8485qww000000000\",\"field_input_name\":\"tonics404handler_404_url\",\"fieldName\":\"404 URL\",\"inputName\":\"tonics404handler_404_url\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter The 404 URL\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App Tonics404Handler Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"3yavies9faa0000000000\",\"field_input_name\":\"tonics404handler_redirect_to\",\"fieldName\":\"Redirect To\",\"inputName\":\"tonics404handler_redirect_to\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Where To Redirect To\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App Tonics404Handler Settings",
    "field_name": "input_select",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"3\",\"field_slug_unique_hash\":\"472p2yko4as0000000000\",\"field_input_name\":\"tonics404handler_redirect_type\",\"fieldName\":\"Redirection Type\",\"inputName\":\"tonics404handler_redirect_type\",\"selectData\":\"301:301 Permanent Redirect,302:302 Temporary Redirect\",\"defaultValue\":\"301\"}"
  }
]
JSON;
        return json_decode($json);
    }

    /**
     * @return FieldData
     */
    public function getFieldData (): FieldData
    {
        return $this->fieldData;
    }
}