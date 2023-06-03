<?php

namespace App\Apps\TonicsCloud;

use App\Apps\TonicsCloud\EventHandlers\CloudMenus;
use App\Apps\TonicsCloud\EventHandlers\CloudServersHandler\LinodeCloudServerHandler;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudContainerImages;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudContainerProfiles;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudInstances;
use App\Apps\TonicsCloud\EventHandlers\Fields\CloudRegions;
use App\Apps\TonicsCloud\EventHandlers\Fields\PricingTable;
use App\Apps\TonicsCloud\EventHandlers\HandleDataTableTemplate;
use App\Apps\TonicsCloud\Events\OnAddCloudServerEvent;
use App\Apps\TonicsCloud\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Field\Events\OnFieldMetaBox;
use Devsrealm\TonicsRouterSystem\Route;

class TonicsCloudActivator implements ExtensionConfig
{
    use Routes;

    const CAN_ACCESS_TONICS_CLOUD = 'TONICS_CAN_ACCESS_TONICS_CLOUD';

    public static function DEFAULT_PERMISSIONS(): array
    {
        return [
            self::CAN_ACCESS_TONICS_CLOUD
        ];
    }

    static array $TABLES = [
        self::TONICS_CLOUD_PROVIDER => [ 'provider_id', 'provider_name', 'provider_perm_name', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_SERVICES => [
            'service_id', 'service_name', 'service_provider_id',
            'service_description', 'service_type',
            'monthly_cap', 'hourly_rate', 'others',
            'created_at', 'updated_at'
        ],

        self::TONICS_CLOUD_SERVICE_INSTANCES => [
            'service_instance_id', 'provider_instance_id', 'service_instance_name', 'service_instance_status', 'fk_service_id',
            'fk_customer_id', 'start_time', 'end_time', 'others', 'created_at', 'updated_at'
        ],

        self::TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG => [ 'log_id', 'service_instance_id', 'log_description', 'usage_data', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_CONTAINERS => [ 'container_id', 'container_name', 'container_description', 'container_status', 'service_instance_id', 'others','created_at', 'updated_at'],

        self::TONICS_CLOUD_CONTAINER_PROFILES => [ 'container_profile_id', 'container_profile_name', 'container_profile_description', 'others','created_at', 'updated_at'],

        self::TONICS_CLOUD_CONTAINER_IMAGES => [ 'container_image_id', 'container_image_name', 'container_image_description', 'others', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_APPS => [ 'app_id', 'app_name', 'app_description', 'app_version', 'others','created_at', 'updated_at'],
        self::TONICS_CLOUD_APPS_TO_CONTAINERS => [ 'id', 'fk_container_id', 'fk_app_id', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_CREDITS => [ 'credit_id', 'credit_amount', 'credit_description', 'fk_customer_id', 'created_at', 'updated_at'],
    ];

    const TONICS_CLOUD_PROVIDER = 'cloud_providers';
    const TONICS_CLOUD_SERVICES = 'cloud_services';
    const TONICS_CLOUD_SERVICE_INSTANCES = 'cloud_service_instances';
    const TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG = 'cloud_service_instance_log';
    const TONICS_CLOUD_CREDITS = 'cloud_credits';
    const TONICS_CLOUD_CONTAINERS = 'cloud_containers';
    const TONICS_CLOUD_CONTAINER_PROFILES = 'cloud_container_profiles';
    const TONICS_CLOUD_CONTAINER_IMAGES = 'cloud_container_images';
    const TONICS_CLOUD_APPS = 'cloud_apps';
    const TONICS_CLOUD_APPS_TO_CONTAINERS = 'cloud_apps_containers';

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * @param Route $routes
     * @return Route
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        $route = $this->routeApi($routes);
        return $this->routeWeb($route);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public static function getCloudServerHandler($name = '')
    {
        /** @var OnAddCloudServerEvent $cloudServer */
        $cloudServer = event()->dispatch(new OnAddCloudServerEvent())->event();
        if ($cloudServer->exist($name)){
            return $cloudServer->getCloudServerHandler($name);
        }

        throw new \Exception("$name is an unknown cloud server handler name");
    }

    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [
            OnAddCloudServerEvent::class => [
                LinodeCloudServerHandler::class
            ],
            OnAdminMenu::class => [
                CloudMenus::class
            ],
            OnFieldMetaBox::class => [
                PricingTable::class,
                CloudRegions::class,
                CloudInstances::class,
                CloudContainerProfiles::class,
                CloudContainerImages::class
            ],
            OnHookIntoTemplate::class => [
                HandleDataTableTemplate::class
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function tables(): array
    {
        return [
            self::getTable(self::TONICS_CLOUD_PROVIDER) => self::$TABLES[self::TONICS_CLOUD_PROVIDER],

            self::getTable(self::TONICS_CLOUD_SERVICES) => self::$TABLES[self::TONICS_CLOUD_SERVICES],
            self::getTable(self::TONICS_CLOUD_SERVICE_INSTANCES) => self::$TABLES[self::TONICS_CLOUD_SERVICE_INSTANCES],
            self::getTable(self::TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG) => self::$TABLES[self::TONICS_CLOUD_SERVICE_INSTANCE_USAGE_LOG],

            self::getTable(self::TONICS_CLOUD_CREDITS) => self::$TABLES[self::TONICS_CLOUD_CREDITS],

            self::getTable(self::TONICS_CLOUD_CONTAINERS) => self::$TABLES[self::TONICS_CLOUD_CONTAINERS],
            self::getTable(self::TONICS_CLOUD_APPS) => self::$TABLES[self::TONICS_CLOUD_APPS],
            self::getTable(self::TONICS_CLOUD_APPS_TO_CONTAINERS) => self::$TABLES[self::TONICS_CLOUD_APPS_TO_CONTAINERS],

            self::getTable(self::TONICS_CLOUD_CONTAINER_IMAGES) => self::$TABLES[self::TONICS_CLOUD_CONTAINER_IMAGES],
        ];
    }

    public static function getTable(string $tableName): string
    {
        if (!key_exists($tableName, self::$TABLES)){
            throw new \InvalidArgumentException("`$tableName` is an invalid table name");
        }

        return DatabaseConfig::getPrefix() . $tableName;
    }

    public function onInstall(): void
    {
        return;
    }

    public function onUninstall(): void
    {
        return;
    }

    public function onUpdate(): void
    {
        return;
    }
    

    public function onDelete(): void
    {
    
    }

    public function info(): array
    {
        return [
            "name" => "TonicsCloud",
            "type" => "App", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-app.1683268481',
            "description" => "This is TonicsCloud",
            "info_url" => '',
            "settings_page" => route('tonicsCloud.settings'), // can be null or a route name
            "update_discovery_url" => "",
            "authors" => [
                "name" => "Your Name",
                "email" => "name@website.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }

}