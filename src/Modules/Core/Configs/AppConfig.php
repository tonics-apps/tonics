<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Configs;


use App\InitLoader;
use App\InitLoaderMinimal;
use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Database;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\Router\RouteResolver;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsDomParser\DomParser;
use Devsrealm\TonicsEventSystem\EventDispatcher;
use Devsrealm\TonicsEventSystem\EventQueue;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Handler\Router;
use Devsrealm\TonicsRouterSystem\RequestInput;
use Devsrealm\TonicsRouterSystem\Response;
use Devsrealm\TonicsRouterSystem\Route;
use Devsrealm\TonicsRouterSystem\RouteNode;
use Devsrealm\TonicsRouterSystem\RouteTreeGenerator;
use Devsrealm\TonicsRouterSystem\State\RouteTreeGeneratorState;
use Exception;

class AppConfig
{
    private static InitLoader|null $init = null;
    private static InitLoaderMinimal|null $initLoaderMinimal = null;

    /**
     * The second entry point into our app after initialization of minimal dependencies, this uses injection sort of to construct all the
     * necessary objects, and caches it, so, it constructs it just once, and the subsequent request might be a bit faster.
     * @param bool $failSilently
     * @return InitLoader
     * @throws Exception
     */
    public static function initLoaderOthers(bool $failSilently = false): InitLoader
    {
        try {
            $initKey = AppConfig::getCachePrefix() . self::getAppCacheKey();
            if (function_exists('apcu_enabled') && apcu_exists($initKey)) {
                $initLoader = apcu_fetch($initKey);
            } else {
                ## Router And Request
                $onRequestProcess = new OnRequestProcess(
                    new RouteResolver(new Container()),
                    new Route(
                        new RouteTreeGenerator(
                            new RouteTreeGeneratorState(), new RouteNode())
                    )
                );

                $router = new Router($onRequestProcess,
                    $onRequestProcess->getRouteObject(),
                    new Response($onRequestProcess, new RequestInput()));

                $modules = helper()->getModuleActivators([ExtensionConfig::class]);
                $apps = helper()->getAppsActivator([ExtensionConfig::class], helper()->getAllAppsDirectory());

                $events = [];
                foreach ($modules as $module) {
                    /** @var $module ExtensionConfig */
                    // you can disable each module in its own config
                    // This gives us the module availability
                    if ($module->enabled()) {
                        // $events = [...$events, ...$module->events()];
                        $events = array_merge_recursive($events, $module->events());
                        // append the Routes by chaining...
                        $module->route($router->getRoute());
                    }
                }

                ## Apps Would Only Appear if they have .installed (which would be added programmatically on installation)
                foreach ($apps as $app) {
                    /** @var $app ExtensionConfig */
                    if ($app->enabled()) {
                        $app->route($router->getRoute());
                        ## The array_intersect_key checks if the apps event array has something in common with the module event($events),
                        # so, I just recursively merge only the intersection (using recursive merging because you might have several events in your modules).
                        $events = array_merge_recursive($events, array_intersect_key($app->events(), $events));
                    }
                }

                $eventQueue = new EventQueue();
                $eventDispatcher = new EventDispatcher($eventQueue->addMultipleEventsAndHandlers($events));

                /**@var TonicsTemplateEngines $tonicsTemplateEngine */
                $tonicsTemplateEngine = $eventDispatcher->dispatch(new TonicsTemplateEngines());
                ## Construct The GrandFather...
                $initLoader = new InitLoader();
                $initLoader
                    ->setRouter($router)
                    ->setTonicsView($tonicsTemplateEngine->getTemplateEngine('Native'))
                    ->setTonicsTemplateEngines($tonicsTemplateEngine)
                    ->setEventDispatcher($eventDispatcher);
                if (function_exists('apcu_enabled')) {
                    apcu_store($initKey, $initLoader);
                }
            }
            if (!self::$init) {
                self::$init = $initLoader;
            }

            return self::$init;
        } catch (Exception $e) {
            if ($failSilently) {
                ## Fail Silently
                exit(1);
            }
            throw $e;
        }
    }

    /**
     * Sets the minimal essential dependencies to keep the app running,
     * this should be resolve first and should be light
     * @param bool $failSilently
     * @return InitLoaderMinimal
     * @throws Exception
     */
    public static function initLoaderMinimal(bool $failSilently = false): InitLoaderMinimal
    {
        try {
            $initKey = AppConfig::getCachePrefix() . self::getAppCacheKey() . '_minimal';
            if (function_exists('apcu_enabled') && apcu_exists($initKey)) {
                $initLoader = apcu_fetch($initKey);
            } else {
                $tonicsHelper = new TonicsHelpers();
                $tonicsHelper->setModulesPath(AppConfig::getModulesPath());
                $tonicsHelper->setAppsPath(AppConfig::getAppsPath());

                ## Construct The GrandFather...
                $initLoader = new InitLoaderMinimal();
                $initLoader
                    ->setContainer(new Container())
                    ->setTonicsHelpers($tonicsHelper)
                    ->setSession(new Session())
                    ->setDomParser(new DomParser());
                if (function_exists('apcu_enabled')) {
                    apcu_store($initKey, $initLoader);
                }
            }
            if (!self::$initLoaderMinimal) {
                self::$initLoaderMinimal = $initLoader;
            }

            return self::$initLoaderMinimal;
        } catch
        (Exception $e) {
            if ($failSilently) {
                ## Fail Silently
                exit(1);
            }
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public static function autoResolvePageRoutes(string $controller, Route $route): Route
    {
        if (helper()->isCLI()){
            return $route;
        }

        $pageTable = Tables::getTable(Tables::PAGES);
        try {
            $pages = db()->run("SELECT * FROM $pageTable");
            foreach ($pages as $page) {
                if ($page->page_status === 1) {
                    # If url has not been chosen or is not a reserved path
                    $foundURLNode = $route->getRouteTreeGenerator()->findURL($page->page_slug);
                    if ($foundURLNode->getFoundURLNode() === null || empty($foundURLNode->getFoundURLNode()->getSettings())){
                        $route->get($page->page_slug, [$controller, 'viewPage'], moreSettings: $page);
                    }
                }
            }
        }catch (Exception){
            // log..
        }

        return $route;
    }

    public static function isMaintenanceMode(): bool
    {
        return (bool)env('MAINTENANCE_MODE', false);
    }

    public static function getTimeZone(): string
    {
        return env('APP_TIME_ZONE', 'UTC');
    }

    public static function getLanguage(): string
    {
        return env('APP_LANGUAGE', '');
    }

    public static function getAppName(): string
    {
        return env('APP_NAME', 'Tonics');
    }

    /**
     * For throttling pagination on the frontend
     * @return string
     */
    public static function getAppPaginationMax(): string
    {
        return env('APP_PAGINATION_MAX_LIMIT', 100);
    }

    public static function getJobTransporter(): string
    {
        return env('JOB_TRANSPORTER', 'DATABASE');
    }

    public static function getJobLimit(): int
    {
        return (int)env('JOB_LIMIT', 10);
    }

    public static function getSchedulerTransporter(): string
    {
        return env('SCHEDULE_TRANSPORTER', 'DATABASE');
    }

    /**
     * Without a cache prefix, multiple sites on the same server might reference each other's cache (which is a bad idea)
     * @return string
     */
    public static function getCachePrefix(): string
    {
        return hash('sha256', env('APP_KEY', 'Tonics'));
    }

    public static function getAppCacheKey(): string
    {
        return 'initLoader_' . env('APP_NAME', 'Tonics');
    }

    public static function getAppEnv(): string
    {
        return env('APP_ENV', 'production');
    }

    public static function isProduction(): bool
    {
        return AppConfig::getAppEnv() === 'production';
    }

    public static function getAppInstallKey(): string
    {
        return env('INSTALL_KEY');
    }

    public static function getAppUpdateKey(): string
    {
        return env('UPDATE_KEY', 'NULL');
    }

    /**
     * If
     * - `true` then, all apps should be auto-updated.
     *  - false, nothing should be auto_updated.
     * - array, then only the array items should be auto_updated
     *
     * @return array|bool
     */
    public static function getAutoUpdateApps(): array|bool
    {
        $update = env('AUTO_UPDATE_APPS', 'NULL');
        return self::handleAutoUpdateReturn($update);
    }

    /**
     * If
     * - `true` then, all modules should be auto-updated.
     *  - false, nothing should be auto_updated.
     * - array, then only the array items should be auto_updated
     *
     * @return array|bool
     */
    public static function getAutoUpdateModules(): array|bool
    {
        $update = env('AUTO_UPDATE_MODULES', 'NULL');
        return self::handleAutoUpdateReturn($update);
    }

    private static function handleAutoUpdateReturn($update): array|bool
    {
        if ($update === '0'){
            return false;
        }

        if ($update === '1'){
            return true;
        }
        $updates = explode(',', $update);
        if (is_array($updates) && !empty($updates)){
            return $updates;
        }
        return false;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public static function getAppUpdatesObject(): mixed
    {
        $globalTable = Tables::getTable(Tables::GLOBAL);
        $updates = db(true)->row("SELECT * FROM $globalTable WHERE `key` = 'updates'");
        if (isset($updates->value) && !empty($updates->value)){
            return json_decode($updates->value, true);
        }
        return [];
    }

    public static function getAppUrl(): string
    {
        return env('APP_URL');
    }

    public static function getAppUrlPort(): string
    {
        return env('APP_URL_PORT');
    }

    public static function getAssetUrl(): string
    {
        return env('ASSET_URL', '');
    }

    public static function getKey(): string
    {
        return env('APP_KEY');
    }

    public static function getModulesPath(): string
    {
        return APP_ROOT . '/src/Modules';
    }

    public static function getAppsPath(): string
    {
        return APP_ROOT . '/src/Apps';
    }

    public static function getETCPath(): string
    {
        return APP_ROOT . '/etc';
    }

    public static function getAppAsset(string $appName, string $appPath): string
    {
        return DriveConfig::serveAppFilePath() . "$appName/?path=$appPath";
    }

    public static function getTranslationsPath(): string
    {
        return APP_ROOT . '/src/Translations';
    }

    public static function getAppRoot(): string
    {
        return APP_ROOT;
    }

    /**
     * Know when to use this
     * @return string
     */
    public static function getPublicPath(): string
    {
        return self::getAppRoot() . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Assets path for plugins or themes
     * @return string
     */
    public static function getThemeAssetPath(): string
    {
        return self::getAppRoot() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'themes';
    }

    /**
     * Include entry-point helpers
     */
    public static function includeHelpers(): void
    {
        require AppConfig::getAppRoot() . '/src/Library/Helpers/all-helpers.php';
    }

    /**
     * Include entry-point helpers
     */
    public static function getEnvFilePath(): string
    {
        return APP_ROOT . DIRECTORY_SEPARATOR . '.env';
    }

    public static function isInternalModuleNameSpace(string $class): bool
    {
        $moduleNameSpace = 'App\Modules';
        return str_starts_with($class, $moduleNameSpace);
    }

    public static function isAppNameSpace(string|object $object_or_class): bool
    {
        if (is_object($object_or_class)){
            $object_or_class = $object_or_class::class;
        }
        $moduleNameSpace = 'App\Apps';
        return str_starts_with($object_or_class, $moduleNameSpace);
    }


}