<?php

namespace App\Apps\TonicsAmazonAffiliate;

use App\Apps\TonicsAmazonAffiliate\EventHandler\TonicsAmazonAffiliateFieldSelection;
use App\Apps\TonicsAmazonAffiliate\EventHandler\TonicsAmazonAffiliateProductBoxFieldHandler;
use App\Apps\TonicsAmazonAffiliate\EventHandler\TonicsAmazonAffiliateProductIndividuallyFieldsFieldHandler;
use App\Apps\TonicsAmazonAffiliate\Route\Routes;
use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Library\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\FieldTemplateFile;
use App\Modules\Field\Events\OnEditorFieldSelection;
use Devsrealm\TonicsRouterSystem\Route;

class TonicsAmazonAffiliateActivator implements ExtensionConfig, FieldItemsExtensionConfig
{
    use Routes;

    static array $TABLES = [
        self::AMAZON_AFFILIATE => [ 'id', 'asin', 'others','created_at', 'updated_at']
    ];

    const AMAZON_AFFILIATE = 'amazon_affiliate';

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
                TonicsAmazonAffiliateFieldSelection::class
            ],

            FieldTemplateFile::class => [
                TonicsAmazonAffiliateProductBoxFieldHandler::class,
                TonicsAmazonAffiliateProductIndividuallyFieldsFieldHandler::class
            ],

        ];
    }

    /**
     * @inheritDoc
     */
    public function tables(): array
    {
        return [
            self::tableName() => self::$TABLES[self::AMAZON_AFFILIATE]
        ];
    }

    /**
     * @throws \Exception
     */
    public function onInstall(): void
    {
        $this->fieldData->importFieldItems($this->fieldItems());
        $this->createDatabaseTable();
    }

    public function onUninstall(): void
    {
        return;
    }

    public function onUpdate(): void
    {
        return;
    }

    /**
     * @throws \Exception
     */
    public function onDelete(): void
    {
        $toDelete = ['app-tonicsamazonaffiliate-product-box', 'app-tonicsamazonaffiliate-product-individually', 'app-tonicsamazonaffiliate-settings'];
        $tb = $this->fieldData->getFieldTable();
        db()->FastDelete($tb, db()->WhereIn(table()->getColumn($tb, 'field_slug'), $toDelete));
        $tableName = self::tableName();
        db()->run("DROP TABLE IF EXISTS `$tableName`");
    }

    /**
     * @throws \Exception
     */
    public function info(): array
    {
        return [
            "name" => "TonicsAmazonAffiliate",
            "type" => "affiliate", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-app.1674540680',
            "description" => "This is TonicsAmazonAffiliate",
            "info_url" => '',
            "settings_page" => route('tonicsAmazonAffiliate.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/app-tonics_amazon_affiliate/releases/latest",
            "authors" => [
                "name" => "Your Name",
                "email" => "name@website.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }

    public function fieldItems(): array
    {
        $json =<<<'JSON'
[
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4z7df1204go0000000000\",\"field_input_name\":\"\",\"fieldName\":\"TonicsAmazonAffiliate Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"24p6i3u9554w000000000\",\"field_input_name\":\"\",\"fieldName\":\"API Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"6dmiq7lqe5o0000000000\",\"field_input_name\":\"tonicsAmazonAffiliateSettings_apiKey\",\"fieldName\":\"API Key\",\"inputName\":\"tonicsAmazonAffiliateSettings_apiKey\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Your Amazon API Key\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"1v96vbz0j7hc000000000\",\"field_input_name\":\"tonicsAmazonAffiliateSettings_apiSecret\",\"fieldName\":\"API Secret\",\"inputName\":\"tonicsAmazonAffiliateSettings_apiSecret\",\"textType\":\"password\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Your Amazon API Secret\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "input_text",
    "field_id": 5,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"52a9txsva780000000000\",\"field_input_name\":\"tonicsAmazonAffiliateSettings_partnerTag\",\"fieldName\":\"Text\",\"inputName\":\"tonicsAmazonAffiliateSettings_partnerTag\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"TrackingID/Partner Tag\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "input_select",
    "field_id": 6,
    "field_parent_id": 2,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"3160krwny1s0000000000\",\"field_input_name\":\"tonicsAmazonAffiliateSettings_region\",\"fieldName\":\"Country/Region\",\"inputName\":\"tonicsAmazonAffiliateSettings_region\",\"selectData\":\"Australia, Belgium, Brazil, Canada, Egypt, France, Germany, India, Italy, Japan, Mexico, Netherlands, Poland, Singapore, Saudi Arabia, Spain, Sweden, Turkey, UAE, UK, USA\",\"defaultValue\":\" USA\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 7,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"74gyp7hzw540000000000\",\"field_input_name\":\"\",\"fieldName\":\"General\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "input_text",
    "field_id": 8,
    "field_parent_id": 7,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"45r7xy14qsk0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Button Text\",\"inputName\":\"tonicsAmazonAffiliateSettings_buttonText\",\"textType\":\"text\",\"defaultValue\":\"Get On Amazon\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Settings",
    "field_name": "input_select",
    "field_id": 9,
    "field_parent_id": 7,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"2ki4nnqg3540000000000\",\"field_input_name\":\"tonicsAmazonAffiliateSettings_cacheDuration\",\"fieldName\":\"Cache Duration\",\"inputName\":\"tonicsAmazonAffiliateSettings_cacheDuration\",\"selectData\":\"24hr:24 Hour,3d:3 Days,1w:1 Week,1m:1 month\",\"defaultValue\":\"1w\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Product Box",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"6ybk74hr2980000000000\",\"field_input_name\":\"\",\"fieldName\":\"Box\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Product Box",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"eq8xw2qkrm0000000000\",\"field_input_name\":\"tonicsAmazonAffiliateProductBox_asin\",\"fieldName\":\"ASIN\",\"inputName\":\"tonicsAmazonAffiliateProductBox_asin\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter ASIN\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Product Box",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3creffvpm200000000000\",\"field_input_name\":\"tonicsAmazonAffiliateProductBox_title\",\"fieldName\":\"Title (Optional)\",\"inputName\":\"tonicsAmazonAffiliateProductBox_title\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Fetches Title From Amazon if Not Set\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Product Box",
    "field_name": "input_rich-text",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"72akgh65nv80000000000\",\"field_input_name\":\"\",\"fieldName\":\"Description (Optional)\",\"inputName\":\"tonicsAmazonAffiliateProductBox_description\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Fetches Description From Amazon if Not Set\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Product Box",
    "field_name": "input_select",
    "field_id": 5,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"95kb4irn8fk000000000\",\"field_input_name\":\"tonicsAmazonAffiliateProductBox_boxType\",\"fieldName\":\"Box Type\",\"inputName\":\"tonicsAmazonAffiliateProductBox_boxType\",\"selectData\":\"Horizontal, Vertical\",\"defaultValue\":\"Horizontal\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Product Individually",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"15nv941kkpcw000000000\",\"field_input_name\":\"\",\"fieldName\":\"Individual Field\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Product Individually",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"5vp265toviw0000000000\",\"field_input_name\":\"tonicsAmazonAffiliateProductIndividual_asin\",\"fieldName\":\"ASIN\",\"inputName\":\"tonicsAmazonAffiliateProductIndividual_asin\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter ASIN\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsAmazonAffiliate Product Individually",
    "field_name": "input_select",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"yqhk2fb9fbk000000000\",\"field_input_name\":\"tonicsAmazonAffiliateProductIndividual_fieldType\",\"fieldName\":\"Field Type\",\"inputName\":\"tonicsAmazonAffiliateProductIndividual_fieldType\",\"selectData\":\"Title, Description, Image, Price, Button, URL, Last Update\",\"defaultValue\":\"Title\"}"
  }
]
JSON;
        return json_decode($json);
    }

    /**
     * @throws \Exception
     */
    public function createDatabaseTable()
    {
        $tableName = self::tableName();

        db()->run("
CREATE TABLE IF NOT EXISTS `$tableName` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,     
    `asin` varchar(10) NOT NULL,
    `others` JSON DEFAULT NULL,
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_key` (`asin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }


    public static function tableName(): string
    {
        return DatabaseConfig::getPrefix() . self::AMAZON_AFFILIATE;
    }

}