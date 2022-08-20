<?php

namespace App\Apps\TonicsToc;

use App\Apps\TonicsToc\EventHandler\EditorsAssetsHandler;
use App\Apps\TonicsToc\EventHandler\TonicsTocFieldHandler;
use App\Apps\TonicsToc\EventHandler\TonicsTocFieldSelection;
use App\Apps\TonicsToc\Route\Routes;
use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\EditorsAsset;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnEditorFieldSelection;
use Devsrealm\TonicsRouterSystem\Route;

class TonicsTocActivator implements ExtensionConfig
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
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        $route = $this->routeApi($routes);
        return $this->routeWeb($route);
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [

            OnEditorFieldSelection::class => [
                TonicsTocFieldSelection::class
            ],

            FieldTemplateFile::class => [
                TonicsTocFieldHandler::class
            ],

            EditorsAsset::class => [
                EditorsAssetsHandler::class
            ]
        ];
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

    public function onUpdate(): void
    {
        return;
    }

    public function getFieldItemsToImport(): array
    {
        $json =<<<'JSON'
[
  {
    "fk_field_id": "App TonicsToc",
    "field_name": "input_text",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"field_slug_unique_hash\":\"65d9zpp703k0000000000\",\"fieldName\":\"TOC Label\",\"inputName\":\"toc_label\",\"textType\":\"text\",\"defaultValue\":\"Table of Content\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"elementWrapper\":\"\",\"attributes\":\"\",\"templateEngine\":\"\",\"nativeTemplateHook\":\"\",\"tonicsTemplateFrag\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsToc",
    "field_name": "modular_fieldfilehandler",
    "field_id": 2,
    "field_parent_id": null,
    "field_options": "{\"field_slug\":\"modular_fieldfilehandler\",\"field_slug_unique_hash\":\"3plfkeao23u0000000000\",\"fieldName\":\"FieldFileHandler\",\"templateFile\":\"App\\\\Apps\\\\TonicsToc\\\\EventHandler\\\\TonicsTocFieldHandler\",\"hideInUserEditForm\":\"1\",\"postEditor\":\"1\"}"
  },
  {
    "fk_field_id": "App TonicsToc Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"10bkndblje4g000000000\",\"fieldName\":\"Label\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsToc Settings",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"7t8dxnbb8zs000000000\",\"fieldName\":\"TOC Label\",\"inputName\":\"toc_label\",\"textType\":\"text\",\"defaultValue\":\"Table of Content\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"elementWrapper\":\"\",\"attributes\":\"\",\"templateEngine\":\"\",\"nativeTemplateHook\":\"\",\"tonicsTemplateFrag\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsToc Settings",
    "field_name": "input_select",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2stx9q02to40000000000\",\"fieldName\":\"Toc Label Tag\",\"inputName\":\"toc_label_tag\",\"selectData\":\"div,span,h1,h2\",\"defaultValue\":\"h2\",\"templateEngine\":\"\",\"nativeTemplateHook\":\"\",\"tonicsTemplateFrag\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsToc Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"432whxwgv3c0000000000\",\"fieldName\":\"Other Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"hideInUserEditForm\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsToc Settings",
    "field_name": "input_text",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"vmj95749d74000000000\",\"fieldName\":\"Toc Class\",\"inputName\":\"toc_class\",\"textType\":\"text\",\"defaultValue\":\"tonics-toc\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"elementWrapper\":\"\",\"attributes\":\"\",\"templateEngine\":\"\",\"nativeTemplateHook\":\"\",\"tonicsTemplateFrag\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsToc Settings",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"nck0mfxn7r4000000000\",\"fieldName\":\"Trigger Toc When Header is Greater Than\",\"inputName\":\"toc_trigger\",\"textType\":\"number\",\"defaultValue\":\"2\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"1\",\"elementWrapper\":\"\",\"attributes\":\"\",\"templateEngine\":\"\",\"nativeTemplateHook\":\"\",\"tonicsTemplateFrag\":\"\"}"
  }
]
JSON;
        return json_decode($json);
    }

    public function info(): array
    {
        return [
            "name" => "TonicsToc",
            "type" => "Tool", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-app.1659921723',
            "description" => "This is TonicsToc",
            "info_url" => '',
            "settings_page" => route('tonicsToc.settings'), // can be null or a route name
            "update_discovery_url" => "",
            "authors" => [
                "name" => "Your Name",
                "email" => "name@website.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
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

    public function onDelete(): void
    {
        // TODO: Implement onDelete() method.
    }
}