<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Customer;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Commands\Module\ModuleMigrate;
use App\Modules\Core\Library\Tables;
use App\Modules\Customer\Routes\RouteWeb;
use Devsrealm\TonicsRouterSystem\Route;

class CustomerActivator implements ExtensionConfig
{
    use RouteWeb;

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

            ];
    }

    /**
     * @throws \ReflectionException
     */
    public function route(Route $routes): Route
    {
        return $this->routeWeb($routes);
    }

    /**
     * @return array
     */
    public function tables(): array
    {
        return
            [
                Tables::getTable(Tables::CUSTOMERS) => Tables::$TABLES[Tables::CUSTOMERS],
            ];
    }

    public function onInstall(): void
    {
        // TODO: Implement onInstall() method.
    }

    public function onUninstall(): void
    {
        // TODO: Implement onUninstall() method.
    }

    public function info(): array
    {
        return [
            "name" => "Customer",
            "type" => "Module",
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-Ola.1675754507',
            // "version" => '1-O-Ola.943905600', // fake old date
            "description" => "The Customer Module",
            "info_url" => '',
            "update_discovery_url" => "https://api.github.com/repos/tonics-apps/tonics-customer-module/releases/latest",
            "authors" => [
                "name" => "The Devsrealm Guy",
                "email" => "faruq@devsrealm.com",
                "role" => "Developer"
            ],
            "credits" => []
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function onUpdate(): void
    {
        self::migrateDatabases();
        return;
    }

    /**
     * @throws \ReflectionException
     */
    public static function migrateDatabases()
    {
        $appMigrate = new ModuleMigrate();
        $commandOptions = [
            '--module' => 'Customer',
            '--migrate' => '',
        ];
        $appMigrate->setIsCLI(false);
        $appMigrate->run($commandOptions);
    }

    public function onDelete(): void
    {
        // TODO: Implement onDelete() method.
    }
}