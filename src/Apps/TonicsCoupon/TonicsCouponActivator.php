<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCoupon;

use App\Apps\TonicsCoupon\Controllers\CouponSettingsController;
use App\Apps\TonicsCoupon\EventHandlers\AssetsHookHandler;
use App\Apps\TonicsCoupon\EventHandlers\CouponMenus;
use App\Apps\TonicsCoupon\EventHandlers\CouponSitemap;
use App\Apps\TonicsCoupon\EventHandlers\CouponTypeSitemap;
use App\Apps\TonicsCoupon\EventHandlers\DefaultCouponFieldHandler;
use App\Apps\TonicsCoupon\EventHandlers\DefaultCouponTypeFieldHandler;
use App\Apps\TonicsCoupon\EventHandlers\Fields\CouponTypeSelect;
use App\Apps\TonicsCoupon\EventHandlers\HandleNewCouponSlugIDGeneration;
use App\Apps\TonicsCoupon\EventHandlers\HandleNewCouponToCouponTypeMapping;
use App\Apps\TonicsCoupon\EventHandlers\HandleNewCouponTypeSlugIDGeneration;
use App\Apps\TonicsCoupon\EventHandlers\HandleUpdateCouponToCouponTypeMapping;
use App\Apps\TonicsCoupon\EventHandlers\PageTemplates\TonicsCouponDefaultPageTemplate;
use App\Apps\TonicsCoupon\Events\OnBeforeCouponSave;
use App\Apps\TonicsCoupon\Events\OnCouponCreate;
use App\Apps\TonicsCoupon\Events\OnCouponDefaultField;
use App\Apps\TonicsCoupon\Events\OnCouponTypeCreate;
use App\Apps\TonicsCoupon\Events\OnCouponTypeDefaultField;
use App\Apps\TonicsCoupon\Events\OnCouponUpdate;
use App\Apps\TonicsCoupon\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\FieldItemsExtensionConfig;
use App\Modules\Core\Commands\App\AppMigrate;
use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Page\Events\OnPageTemplate;
use Devsrealm\TonicsRouterSystem\Route;

class TonicsCouponActivator implements ExtensionConfig, FieldItemsExtensionConfig
{
    use Routes;

    const COUPON         = 'coupon';
    const COUPON_TYPE    = 'coupon_type';
    const COUPON_TO_TYPE = 'coupon_to_type';
    static array      $TABLES = [
        self::COUPON         => [
            'coupon_id', 'slug_id', 'coupon_name', 'image_url', 'coupon_slug', 'user_id', 'coupon_status', 'field_settings', 'created_at', 'started_at', 'expired_at', 'updated_at',
        ],
        self::COUPON_TYPE    => [
            'coupon_type_id', 'slug_id', 'coupon_type_parent_id', 'coupon_type_name', 'coupon_type_slug', 'coupon_type_status', 'field_settings', 'created_at', 'updated_at',
        ],
        self::COUPON_TO_TYPE => [
            'id', 'fk_coupon_type_id', 'fk_coupon_id', 'created_at', 'updated_at',
        ],
    ];
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

    /**
     * @param Route $routes
     *
     * @return Route
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
            OnAdminMenu::class => [
                CouponMenus::class,
            ],

            OnFieldMetaBox::class => [
                CouponTypeSelect::class,
            ],

            OnBeforeCouponSave::class => [
            ],

            OnCouponTypeCreate::class => [
                HandleNewCouponTypeSlugIDGeneration::class,
            ],

            OnCouponCreate::class => [
                HandleNewCouponSlugIDGeneration::class,
                HandleNewCouponToCouponTypeMapping::class,
            ],

            OnCouponUpdate::class => [
                HandleUpdateCouponToCouponTypeMapping::class,
            ],

            OnCouponDefaultField::class => [
                DefaultCouponFieldHandler::class,
            ],

            OnCouponTypeDefaultField::class => [
                DefaultCouponTypeFieldHandler::class,
            ],

            OnPageTemplate::class => [
                TonicsCouponDefaultPageTemplate::class,
            ],

            OnHookIntoTemplate::class => [
                AssetsHookHandler::class,
            ],

            OnAddSitemap::class => [
                CouponSitemap::class,
                CouponTypeSitemap::class,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function tables (): array
    {
        return [
            self::couponTableName()       => self::$TABLES[self::COUPON],
            self::couponTypeTableName()   => self::$TABLES[self::COUPON_TYPE],
            self::couponToTypeTableName() => self::$TABLES[self::COUPON_TO_TYPE],
        ];
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function onInstall (): void
    {
        $this->fieldData->importFieldItems($this->fieldItems());
        self::migrateDatabases();
        return;
    }

    public function onUninstall (): void
    {
        return;
    }

    /**
     * @throws \ReflectionException
     */
    public function onUpdate (): void
    {
        self::migrateDatabases();
        return;
    }

    public function onDelete (): void
    {
        return;
    }

    /**
     * @throws \Exception
     */
    public function info (): array
    {
        return [
            "name"                 => "TonicsCoupon",
            "type"                 => "Module", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version"              => '1-O-app.1718095500',
            "description"          => "This is TonicsCoupon",
            "info_url"             => '',
            "settings_page"        => route('tonicsCoupon.settings'), // can be null or a route name
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/app-tonics_coupon/releases/latest",
            "authors"              => [
                "name"  => "Your Name",
                "email" => "name@website.com",
                "role"  => "Developer",
            ],
            "credits"              => [],
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public static function migrateDatabases ()
    {
        $appMigrate = new AppMigrate();
        $commandOptions = [
            '--app'     => 'TonicsCoupon',
            '--migrate' => '',
        ];
        $appMigrate->setIsCLI(false);
        $appMigrate->run($commandOptions);
    }

    public static function couponTableName (): string
    {
        return DatabaseConfig::getPrefix() . self::COUPON;
    }

    public static function couponTypeTableName (): string
    {
        return DatabaseConfig::getPrefix() . self::COUPON_TYPE;
    }

    public static function couponToTypeTableName (): string
    {
        return DatabaseConfig::getPrefix() . self::COUPON_TO_TYPE;
    }

    /**
     * @param array $coupon
     *
     * @return string
     * @throws \Exception
     */
    public static function getCouponAbsoluteURLPath (array $coupon): string
    {
        $rootPath = CouponSettingsController::getTonicsCouponRootPath();
        if (isset($coupon['slug_id']) && isset($coupon['coupon_slug'])) {
            return "/$rootPath/{$coupon['slug_id']}/{$coupon['coupon_slug']}";
        }

        return '';
    }

    /**
     * @param array $coupon
     *
     * @return string
     * @throws \Exception
     */
    public static function getCouponTypeAbsoluteURLPath (array $coupon): string
    {
        $rootPath = CouponSettingsController::getTonicsCouponTypeRootPath();
        if (isset($coupon['slug_id']) && isset($coupon['coupon_type_slug'])) {
            return "/$rootPath/{$coupon['slug_id']}/{$coupon['coupon_type_slug']}";
        }

        return '';
    }

    function fieldItems (): array
    {
        $json = <<<'JSON'
[
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"4n37iv3fafi0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_coupon_experience\",\"fieldName\":\"Coupon Experience\",\"inputName\":\"app_tonicscoupon_coupon_page_coupon_experience\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"258gcabkc02o000000000\",\"field_input_name\":\"coupon_name\",\"fieldName\":\"Coupon Name\",\"inputName\":\"coupon_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Name Here\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"2d3opaz8htc0000000000\",\"field_input_name\":\"coupon_label\",\"fieldName\":\"Coupon Label\",\"inputName\":\"coupon_label\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"E.g Up To 40% Off\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_rich-text",
    "field_id": 4,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"1dddplgrsx9c000000000\",\"field_input_name\":\"coupon_content\",\"fieldName\":\"Coupon Content\",\"inputName\":\"coupon_content\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Coupon Content\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"2kl3opsv4jk0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_coupon_settings\",\"fieldName\":\"Coupon Settings\",\"inputName\":\"app_tonicscoupon_coupon_page_coupon_settings\",\"row\":\"2\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "media_media-image",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"media_media-image\",\"media_media-image_cell\":\"1\",\"field_slug_unique_hash\":\"292gs1yqsftw000000000\",\"field_input_name\":\"image_url\",\"fieldName\":\"Featured Image\",\"inputName\":\"image_url\",\"imageLink\":\"\",\"featured_image\":\"\",\"defaultImage\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 7,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"29y3f76flge8000000000\",\"field_input_name\":\"\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_text",
    "field_id": 8,
    "field_parent_id": 7,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3puok445qv00000000000\",\"field_input_name\":\"coupon_slug\",\"fieldName\":\"Coupon Slug\",\"inputName\":\"coupon_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Coupon Slug (optional)\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_select",
    "field_id": 9,
    "field_parent_id": 7,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"4wrf7sacesy0000000000\",\"field_input_name\":\"coupon_status\",\"fieldName\":\"Status\",\"inputName\":\"coupon_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_text",
    "field_id": 10,
    "field_parent_id": 7,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"71oprt0tuhw0000000000\",\"field_input_name\":\"coupon_url\",\"fieldName\":\"Coupon URL\",\"inputName\":\"coupon_url\",\"textType\":\"url\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Coupon URL\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "tonicscoupon_coupontypeselect",
    "field_id": 11,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"tonicscoupon_coupontypeselect\",\"tonicscoupon_coupontypeselect_cell\":\"2\",\"field_slug_unique_hash\":\"kvbxr85jtls000000000\",\"field_input_name\":\"fk_coupon_type_id\",\"fieldName\":\"Coupon Types\",\"inputName\":\"fk_coupon_type_id\",\"multipleSelect\":\"1\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "post_postauthorselect",
    "field_id": 12,
    "field_parent_id": 5,
    "field_options": "{\"field_slug\":\"post_postauthorselect\",\"post_postauthorselect_cell\":\"3\",\"field_slug_unique_hash\":\"3arrz4vab7k0000000000\",\"field_input_name\":\"user_id\",\"fieldName\":\"Author\",\"inputName\":\"user_id\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 13,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"4\",\"field_slug_unique_hash\":\"3ctih0y27640000000000\",\"field_input_name\":\"\",\"fieldName\":\"Date\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_date",
    "field_id": 14,
    "field_parent_id": 13,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"60xj2s4nruo0000000000\",\"field_input_name\":\"created_at\",\"fieldName\":\"Created Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_date",
    "field_id": 15,
    "field_parent_id": 13,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"5g8tr9kqblc0000000000\",\"field_input_name\":\"start_at\",\"fieldName\":\"Start Date\",\"inputName\":\"started_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page]",
    "field_name": "input_date",
    "field_id": 16,
    "field_parent_id": 13,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"zs9q1bqi8j4000000000\",\"field_input_name\":\"expired_at\",\"fieldName\":\"Expired Date\",\"inputName\":\"expired_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"2tsxtlkpa2c0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Coupon Type Experience\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4hcvurripyw0000000000\",\"field_input_name\":\"coupon_type_name\",\"fieldName\":\"Coupon Type Name\",\"inputName\":\"coupon_type_name\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Enter Name Here\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "input_rich-text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[\"PostContentEditor\"],\"field_slug\":\"input_rich-text\",\"input_rich-text_cell\":\"1\",\"field_slug_unique_hash\":\"6eme3lds96k0000000000\",\"field_input_name\":\"coupon_type_content\",\"fieldName\":\"Coupon Type Content\",\"inputName\":\"coupon_type_content\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"You can start writing...\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 4,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"2v6rpf6kqtk0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Coupon Type Settings\",\"inputName\":\"\",\"row\":\"2\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "modular_rowcolumn",
    "field_id": 5,
    "field_parent_id": 4,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"1\",\"field_slug_unique_hash\":\"6hu83jkvc780000000000\",\"field_input_name\":\"\",\"fieldName\":\"Meta\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"1\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3ubg2p2tcdu0000000000\",\"field_input_name\":\"coupon_type_slug\",\"fieldName\":\"Coupon Type Slug\",\"inputName\":\"coupon_type_slug\",\"textType\":\"text\",\"defaultValue\":\"\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"Optional\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "input_select",
    "field_id": 7,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"707w7llph000000000000\",\"field_input_name\":\"coupon_type_status\",\"fieldName\":\"Coupon Type Status\",\"inputName\":\"coupon_type_status\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "input_date",
    "field_id": 8,
    "field_parent_id": 5,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_date\",\"input_date_cell\":\"1\",\"field_slug_unique_hash\":\"5i5g4pi3h3g0000000000\",\"field_input_name\":\"created_at\",\"fieldName\":\"Created Date\",\"inputName\":\"created_at\",\"dateType\":\"datetime-local\",\"min\":\"\",\"max\":\"\",\"readonly\":\"0\",\"required\":\"0\",\"defaultValue\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Type Page]",
    "field_name": "tonicscoupon_coupontypeselect",
    "field_id": 9,
    "field_parent_id": 4,
    "field_options": "{\"field_slug\":\"tonicscoupon_coupontypeselect\",\"tonicscoupon_coupontypeselect_cell\":\"2\",\"field_slug_unique_hash\":\"4qmai3o253e0000000000\",\"field_input_name\":\"coupon_type_parent_id\",\"fieldName\":\"Parent Coupon Type\",\"inputName\":\"coupon_type_parent_id\",\"multipleSelect\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"5ah5c8fhde40000000000\",\"field_input_name\":\"\",\"fieldName\":\"Coupon Page Import Settings\",\"inputName\":\"\",\"row\":\"3\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "media_media-manager",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_slug\":\"media_media-manager\",\"media_media-manager_cell\":\"1\",\"field_slug_unique_hash\":\"3wg5hnrn4u00000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_file_URL\",\"fieldName\":\"Import File (Local JSON)\",\"inputName\":\"app_tonicscoupon_coupon_page_import_file_URL\",\"featured_link\":\"\",\"file_url\":\"\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "modular_rowcolumn",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"2\",\"field_slug_unique_hash\":\"731fzxyh88s0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Map Field\",\"inputName\":\"\",\"row\":\"6\",\"column\":\"2\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 4,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"7fk9pqvg5wc0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponName\",\"fieldName\":\"Coupon Name\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponName\",\"textType\":\"text\",\"defaultValue\":\"coupon_name\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 5,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"2\",\"field_slug_unique_hash\":\"mfumykpf1lc000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponLabel\",\"fieldName\":\"Coupon Label\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponLabel\",\"textType\":\"text\",\"defaultValue\":\"coupon_label\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 6,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"3\",\"field_slug_unique_hash\":\"58edx9sb28g0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponContent\",\"fieldName\":\"Coupon Content\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponContent\",\"textType\":\"text\",\"defaultValue\":\"coupon_content\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "modular_rowcolumn",
    "field_id": 7,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"4\",\"field_slug_unique_hash\":\"6mbb55gou0o0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Coupon Status Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 8,
    "field_parent_id": 7,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3365115q7ao0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponStatus\",\"fieldName\":\"Coupon Status\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponStatus\",\"textType\":\"text\",\"defaultValue\":\"coupon_status\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_select",
    "field_id": 9,
    "field_parent_id": 7,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"1\",\"field_slug_unique_hash\":\"56hyjz4gcjg0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponStatusDefaultTo\",\"fieldName\":\"Default To\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponStatusDefaultTo\",\"selectData\":\"0:Draft,1:Publish,-1:Trash\",\"defaultValue\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 10,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"5\",\"field_slug_unique_hash\":\"2wr2cjjjtb40000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponCreatedDate\",\"fieldName\":\"Coupon Created Date\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponCreatedDate\",\"textType\":\"text\",\"defaultValue\":\"created_at\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 11,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"6\",\"field_slug_unique_hash\":\"7fjuse7dbbs0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponExpiredDate\",\"fieldName\":\"Coupon Expried Date\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponExpiredDate\",\"textType\":\"text\",\"defaultValue\":\"expired_at\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 12,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"7\",\"field_slug_unique_hash\":\"3sazsjq8d060000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponImageURL\",\"fieldName\":\"Coupon Image URL\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponImageURL\",\"textType\":\"text\",\"defaultValue\":\"image_url\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 13,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"8\",\"field_slug_unique_hash\":\"qvu670qwdow000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponURL\",\"fieldName\":\"Coupon URL\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponURL\",\"textType\":\"text\",\"defaultValue\":\"coupon_url\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "modular_rowcolumn",
    "field_id": 14,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"modular_rowcolumn_cell\":\"9\",\"field_slug_unique_hash\":\"4x3fnbgqysq0000000000\",\"field_input_name\":\"\",\"fieldName\":\"Coupon Type Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"0\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 15,
    "field_parent_id": 14,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"ah7a15824i0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponType\",\"fieldName\":\"Coupon Type (Optional)(slug)\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponType\",\"textType\":\"text\",\"defaultValue\":\"coupon_type\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "tonicscoupon_coupontypeselect",
    "field_id": 16,
    "field_parent_id": 14,
    "field_options": "{\"field_slug\":\"tonicscoupon_coupontypeselect\",\"tonicscoupon_coupontypeselect_cell\":\"1\",\"field_slug_unique_hash\":\"1zf289dpk2rk000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponTypeDefaultTo\",\"fieldName\":\"Default To\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponTypeDefaultTo\",\"multipleSelect\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "post_postauthorselect",
    "field_id": 17,
    "field_parent_id": 3,
    "field_options": "{\"field_slug\":\"post_postauthorselect\",\"post_postauthorselect_cell\":\"10\",\"field_slug_unique_hash\":\"76mft0ue6hs0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponTypeUserID\",\"fieldName\":\"Author\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponTypeUserID\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 18,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"11\",\"field_slug_unique_hash\":\"71027bfep800000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_mapField_couponURL\",\"fieldName\":\"Coupon URL\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponURL\",\"textType\":\"text\",\"defaultValue\":\"coupon_url\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_text",
    "field_id": 19,
    "field_parent_id": 3,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"12\",\"field_slug_unique_hash\":\"1g6pmhojcy8w000000000\",\"field_input_name\":\"\",\"fieldName\":\"Coupon Started Date\",\"inputName\":\"app_tonicscoupon_coupon_page_import_mapField_couponStartedDate\",\"textType\":\"text\",\"defaultValue\":\"started_at\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon [Coupon Page Import Settings]",
    "field_name": "input_select",
    "field_id": 20,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_select\",\"input_select_cell\":\"3\",\"field_slug_unique_hash\":\"4swbfwb1exi0000000000\",\"field_input_name\":\"app_tonicscoupon_coupon_page_import_importImage\",\"fieldName\":\"Import Image\",\"inputName\":\"app_tonicscoupon_coupon_page_import_importImage\",\"selectData\":\"1:True,0:False\",\"defaultValue\":\"1\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon Settings",
    "field_name": "modular_rowcolumn",
    "field_id": 1,
    "field_parent_id": null,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"modular_rowcolumn\",\"field_slug_unique_hash\":\"3mlvluwqums000000000\",\"field_input_name\":\"\",\"fieldName\":\"Settings\",\"inputName\":\"\",\"row\":\"1\",\"column\":\"1\",\"grid_template_col\":\"\",\"hideInUserEditForm\":\"0\",\"useTab\":\"1\",\"group\":\"0\",\"cell\":\"on\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon Settings",
    "field_name": "input_text",
    "field_id": 2,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"4led39jlyj20000000000\",\"field_input_name\":\"tonicsCoupon_root_path\",\"fieldName\":\"Coupon Root Path\",\"inputName\":\"tonicsCoupon_root_path\",\"textType\":\"text\",\"defaultValue\":\"coupon\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  },
  {
    "fk_field_id": "App TonicsCoupon Settings",
    "field_name": "input_text",
    "field_id": 3,
    "field_parent_id": 1,
    "field_options": "{\"field_validations\":[],\"field_sanitization\":[],\"field_slug\":\"input_text\",\"input_text_cell\":\"1\",\"field_slug_unique_hash\":\"3ja159r27020000000000\",\"field_input_name\":\"tonicsCoupon_root_path\",\"fieldName\":\"Coupon Type Root Path\",\"inputName\":\"tonicsCouponType_root_path\",\"textType\":\"text\",\"defaultValue\":\"coupon_type\",\"hideInUserEditForm\":\"0\",\"maxChar\":\"\",\"placeholder\":\"\",\"readOnly\":\"0\",\"required\":\"0\"}"
  }
]
JSON;
        return json_decode($json);
    }
}