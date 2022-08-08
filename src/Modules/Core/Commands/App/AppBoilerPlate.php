<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands\App;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * To create a new app boilerplate, run: php bin/console --app=app_name --type
 *
 * --type can contain the app category (e.g App, Theme, Module, Tools, etc) or leave it empty
 */
class AppBoilerPlate implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return  [
            "--app",
            "--type"
        ];
    }

    /**
     * @param array $commandOptions
     * @return void
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $result = true;
        $helper = helper();
        $s = DIRECTORY_SEPARATOR;
        $appBoilerPlateExample = APP_ROOT . "{$s}src{$s}Modules{$s}Core{$s}Commands{$s}Module{$s}Template{$s}AppExample";
        if (empty($commandOptions['--app'])){
            $this->errorMessage('--app name is empty');
        } else {
            $appName = helper()->normalizeFileName($commandOptions['--app'], '');
            $appName = str_replace(['-', '_'], ' ', $appName);
            $appName = str_replace(' ', '', ucwords(strtolower($appName)));
            $dest = AppConfig::getAppsPath() . $s . $appName;

            if ($helper->fileExists($dest)){
                $this->errorMessage($appName . ' already exist');
            }elseif ($helper->isReadable(AppConfig::getAppsPath()) && $helper->isWritable(AppConfig::getAppsPath())){
                $result = $helper->copyFolder($appBoilerPlateExample, $dest);
                $activatorCopy = $helper->replacePlaceHolders($this->getAppActivatorString(), [
                    '{{AppType}}' => $commandOptions['--type'] ?: 'App',
                    '{{AppExample}}' => $appName,
                    '{{timestamp}}' => time()
                ]);
                $activatorPath = $dest . $s . $appName .'Activator.php';
                if ($result && @file_put_contents($activatorPath, $activatorCopy) !== false){
                    foreach ($helper->recursivelyScanDirectory($dest) as $file){
                        if (!$file->isReadable() || !$file->isWritable()){
                            $result = false; break;
                        }
                        if ($file->isFile()){
                            $templateToCopy = $helper->replacePlaceHolderInAFile($file->getRealPath(), [
                                '{{AppExample}}' => $appName,
                                '{{timestamp}}' => time()
                            ]);
                            $res = file_put_contents($file->getRealPath(), $templateToCopy);
                            if ($res === false) {
                                $result = false;
                            }
                        }
                    }
                }
            }

        }

        if (!$result){
            $this->errorMessage('An Error Occurred Creating App Boiler Plate');
        }
    }

    private function getAppActivatorString(): string
    {
        return <<<'PHP'
<?php

namespace App\Apps\{{AppExample}};

use App\Apps\{{AppExample}}\Route\Routes;
use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsRouterSystem\Route;

class {{AppExample}}Activator implements ModuleConfig, PluginConfig
{
    use Routes;

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
        return [];
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

    public function info(): array
    {
        return [
            "name" => "{{AppExample}}",
            "type" => "{{AppType}}", // You can change it to 'Theme', 'Tools', 'Modules' or Any Category Suited for Your App
            // the first portion is the version number, the second is the code name and the last is the timestamp
            "version" => '1-O-app.{{timestamp}}',
            "description" => "This is {{AppExample}}",
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
PHP;

    }

}