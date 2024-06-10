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

namespace App\Apps\TonicsAI;

use App\Apps\TonicsAI\EventHandlers\EditorsAssetsHandler;
use App\Apps\TonicsAI\EventHandlers\TonicsAIOpenAIChatFieldHandler;
use App\Apps\TonicsAI\EventHandlers\TonicsAIOpenAIChatFieldSelection;
use App\Apps\TonicsAI\EventHandlers\TonicsAIOpenAIImageFieldHandler;
use App\Apps\TonicsAI\EventHandlers\TonicsAIOpenAIImageFieldSelection;
use App\Apps\TonicsAI\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\EditorsAsset;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnEditorFieldSelection;
use Devsrealm\TonicsRouterSystem\Route;

class TonicsAIActivator implements ExtensionConfig
{
    use Routes;

    private FieldData $fieldData;

    public function __construct (FieldData $fieldData)
    {
        $this->fieldData = $fieldData;
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
        return [
            OnEditorFieldSelection::class => [
                TonicsAIOpenAIChatFieldSelection::class,
                TonicsAIOpenAIImageFieldSelection::class,
            ],

            FieldTemplateFile::class => [
                TonicsAIOpenAIChatFieldHandler::class,
                TonicsAIOpenAIImageFieldHandler::class,
            ],

            EditorsAsset::class => [
                EditorsAssetsHandler::class,
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
        return;
    }

    public function onUninstall (): void
    {
        return;
    }

    public function onUpdate (): void
    {
        return;
    }


    public function onDelete (): void {}

    public function info (): array
    {
        return [
            "name"                 => "TonicsAI",
            "type"                 => "Module", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            "slug_id"              => 'c973e5b6-276c-11ef-9736-124c30cfdb6b',
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-app.1718095500',
            "description"          => "This is TonicsAI",
            "info_url"             => '',
            "settings_page"        => route('tonicsAI.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/app-tonics_ai/releases/latest",
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
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"10l89nlk43hs000000000\",\"field_input_name\":\"tonics_ai_main_container\",\"fieldName\":\"TonicsAI\",\"inputName\":\"tonics_ai_main_container\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"ybcv15nor9s000000000\",\"field_input_name\":\"tonics_ai_open_ai\",\"fieldName\":\"OpenAI\",\"inputName\":\"tonics_ai_open_ai\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6t2g0rz87to0000000000\",\"field_input_name\":\"tonics_ai_open_ai_key\",\"fieldName\":\"KEY\",\"inputName\":\"tonics_ai_open_ai_key\",\"textType\":\"password\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter OpenAI Secret Key\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"7aa977nrd8k0000000000\",\"field_input_name\":\"tonics_ai_open_ai_models\",\"fieldName\":\"Models\",\"inputName\":\"tonics_ai_open_ai_models\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"5z538e88lww0000000000\",\"field_input_name\":\"tonics_ai_open_ai_models_chat\",\"fieldName\":\"Chat\",\"inputName\":\"tonics_ai_open_ai_models_chat\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "input_select",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"1ktgop2jk2w0000000000\",\"field_input_name\":\"tonics_ai_open_ai_models_chat_model_name\",\"fieldName\":\"Choose Model\",\"inputName\":\"tonics_ai_open_ai_models_chat_model_name\",\"selectData\":\"gpt-4,gpt-4-0314,gpt-4-32k,gpt-4-32k-0314,gpt-3.5-turbo,gpt-3.5-turbo-0301\",\"defaultValue\":\"gpt-3.5-turbo\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 7,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"1vs9735npxds000000000\",\"field_input_name\":\"tonics_ai_open_ai_models_complete\",\"fieldName\":\"Complete\",\"inputName\":\"tonics_ai_open_ai_models_complete\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "input_select",
    "field_id": 8,
    "field_parent_id": 7,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4p04sirivdi0000000000\",\"field_input_name\":\"tonics_ai_open_ai_models_complete_model_name\",\"fieldName\":\"Choose Model\",\"inputName\":\"tonics_ai_open_ai_models_complete_model_name\",\"selectData\":\"text-davinci-003,text-davinci-002,text-curie-001,text-babbage-001,text-ada-001,davinci,curie,babbage,ada\",\"defaultValue\":\"text-davinci-003\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 9,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"72ouca31vl80000000000\",\"field_input_name\":\"\",\"fieldName\":\"More...\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAI Settings",
    "field_name": "input_text",
    "field_id": 10,
    "field_parent_id": 9,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1npmpyayulpc000000000\",\"field_input_name\":\"\",\"fieldName\":\"Coming Soon\",\"inputName\":\"\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Coming Soon\",\"readOnly\":\"1\",\"required\":\"1\"}"
  },
  {
    "fk_field_id": "App TonicsAI [OpenAI] Chat",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"1g69g2ogyshs000000000\",\"field_input_name\":\"\",\"fieldName\":\"Chat\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAI [OpenAI] Chat",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"365mszmep4a0000000000\",\"field_input_name\":\"app_tonicsai_openai_chat_message\",\"fieldName\":\"Message\",\"inputName\":\"app_tonicsai_openai_chat_message\",\"textType\":\"textarea\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter a Prompt Message Here...\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAI [OpenAI] Image",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"33rq5ahjoz60000000000\",\"field_input_name\":\"\",\"fieldName\":\"Image\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAI [OpenAI] Image",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"19lmurox07c0000000000\",\"field_input_name\":\"app_tonicsai_openai_image_message\",\"fieldName\":\"Message\",\"inputName\":\"app_tonicsai_openai_image_message\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Image of a man building a house\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAI [OpenAI] Image",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6hj0by9ckk00000000000\",\"field_input_name\":\"app_tonicsai_openai_image_nToGenerate\",\"fieldName\":\"Numbers To Generate\",\"inputName\":\"app_tonicsai_openai_image_nToGenerate\",\"textType\":\"number\",\"defaultValue\":\"1\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"2\",\"placeholder\":\"Numbers of Image To Generate\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAI [OpenAI] Image",
    "field_name": "input_select",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4147gd4brig0000000000\",\"field_input_name\":\"app_tonicsai_openai_image_size\",\"fieldName\":\"Size\",\"inputName\":\"app_tonicsai_openai_image_size\",\"selectData\":\"256:256x256,512:512x512,1024:1024x1024\",\"defaultValue\":\"512x512\"}"
  }
]
JSON;
        return json_decode($json);

    }

}