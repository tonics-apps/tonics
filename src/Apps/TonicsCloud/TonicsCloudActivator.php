<?php

namespace App\Apps\TonicsCloud;

use App\Apps\TonicsCloud\Route\Routes;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsRouterSystem\Route;

class TonicsCloudActivator implements ExtensionConfig
{
    use Routes;

    static array $TABLES = [
        self::TONICS_CLOUD_PROVIDER => [ 'provider_id', 'provider_name', 'provider_perm_name', 'provider_description', 'others','created_at', 'updated_at'],

        self::TONICS_CLOUD_SERVICES => [ 'service_id', 'service_name', 'service_provider_id', 'monthly_cap', 'hourly_rate', 'others', 'created_at', 'updated_at'],
        self::TONICS_CLOUD_SERVICE_INSTANCES => [ 'service_instance_id', 'fk_service_id', 'fk_customer_id', 'start_time', 'end_time', 'created_at', 'updated_at'],
        self::TONICS_CLOUD_CREDITS => [ 'credit_id', 'credit_amount', 'credit_description', 'fk_customer_id', 'created_at', 'updated_at'],

        self::TONICS_CLOUD_CONTAINERS => [ 'container_id', 'container_name', 'container_description', 'container_service_id', 'others','created_at', 'updated_at'],
        self::TONICS_CLOUD_APPS => [ 'app_id', 'app_name', 'app_description', 'app_version', 'others','created_at', 'updated_at'],
        self::TONICS_CLOUD_APPS_TO_CONTAINERS => [ 'id', 'fk_container_id', 'fk_app_id', 'created_at', 'updated_at'],
    ];

    const TONICS_CLOUD_PROVIDER = 'cloud_providers';
    const TONICS_CLOUD_SERVICES = 'cloud_services';
    const TONICS_CLOUD_SERVICE_INSTANCES = 'cloud_service_instances';
    const TONICS_CLOUD_CREDITS = 'cloud_credits';
    const TONICS_CLOUD_CONTAINERS = 'cloud_containers';
    const TONICS_CLOUD_APPS = 'cloud_apps';
    const TONICS_CLOUD_APPS_TO_CONTAINERS = 'cloud_apps_containers';

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
        return [];
    }

    /**
     * @inheritDoc
     */
    public function tables(): array
    {
        return [
            self::getTable(self::TONICS_CLOUD_PROVIDER) => self::$TABLES[self::TONICS_CLOUD_PROVIDER],
            self::getTable(self::TONICS_CLOUD_CONTAINERS) => self::$TABLES[self::TONICS_CLOUD_CONTAINERS],
            self::getTable(self::TONICS_CLOUD_APPS) => self::$TABLES[self::TONICS_CLOUD_APPS],
            self::getTable(self::TONICS_CLOUD_APPS_TO_CONTAINERS) => self::$TABLES[self::TONICS_CLOUD_APPS_TO_CONTAINERS],
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
            "settings_page" => null, // can be null or a route name
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