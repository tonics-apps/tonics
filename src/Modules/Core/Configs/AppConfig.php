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


use App\Modules\Core\Boot\InitLoader;
use App\Modules\Core\Boot\InitLoaderMinimal;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Controllers\CoreSettingsController;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Jobs\UpdateMigrations;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Router\RouteResolver;
use App\Modules\Core\Library\Tables;
use App\Modules\Menu\Data\MenuData;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsDomParser\DomParser;
use Devsrealm\TonicsEventSystem\EventDispatcher;
use Devsrealm\TonicsEventSystem\EventQueue;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Events\OnRequestProcess;
use Devsrealm\TonicsRouterSystem\Handler\Router;
use Devsrealm\TonicsRouterSystem\RequestInput;
use Devsrealm\TonicsRouterSystem\Response;
use Devsrealm\TonicsRouterSystem\Route;
use Devsrealm\TonicsRouterSystem\RouteNode;
use Devsrealm\TonicsRouterSystem\RouteTreeGenerator;
use Devsrealm\TonicsRouterSystem\State\RouteTreeGeneratorState;
use Devsrealm\TonicsTreeSystem\Node;
use Devsrealm\TonicsTreeSystem\Tree;
use Devsrealm\TonicsTreeSystem\TreeGenerator;
use Devsrealm\TonicsTreeSystem\TreeGeneratorState;
use Exception;

class AppConfig
{
    private static InitLoader|null $init = null;
    private static InitLoaderMinimal|null $initLoaderMinimal = null;

    private static function getInitKey(): string
    {
        return AppConfig::getCachePrefix() . self::getAppCacheKey();
    }

    /**
     * The second entry point into our app after initialization of minimal dependencies, this uses injection sort of to construct all the
     * necessary objects, and caches it, so, it constructs it just once, and the subsequent request might be a bit faster.
     * @param bool $failSilently
     * @return InitLoader
     * @throws Exception
     * @throws \Throwable
     */
    public static function initLoaderOthers(bool $failSilently = false): InitLoader
    {
        try {
            $initKey = self::getInitKey();
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

                $treeGenerator = new TreeGenerator(new TreeGeneratorState(), new Node());
                $tree = new Tree($treeGenerator);

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

                ## Apps Would Only Appear if one, TonicsIsReady (meaning TonicsCMS has been installed) and each app
                # have .installed (which would be added programmatically on app installation)
                if (AppConfig::TonicsIsReady()){
                    foreach ($apps as $app) {
                        /** @var $app ExtensionConfig */
                        if ($app->enabled()) {
                            $app->route($router->getRoute());
                            $appEvents = $app->events();
                            foreach ($appEvents as $appEvent => $appEventHandler) {
                                #
                                # if the apps event array has something in common with the module event($events)
                                # we Merger 'em, else, we create a new event.
                                #
                                if (key_exists($appEvent, $events)) {
                                    $moduleEventHandler = $events[$appEvent];
                                    $moduleEventHandler = [...$moduleEventHandler, ...$appEventHandler];
                                    $events[$appEvent] = $moduleEventHandler;
                                } else {
                                    $events[$appEvent] = $appEventHandler;
                                }
                            }
                        }
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
                    ->setTree($tree)
                    ->setEventDispatcher($eventDispatcher);

                if (function_exists('apcu_enabled')) {
                    apcu_store($initKey, $initLoader);
                }

                if (helper()->isNotCLI() && self::isLoggedIn()){
                    self::initAdminMenu();
                    $initLoader->setTree(tree());
                    apcu_delete($initKey);
                    apcu_store($initKey, $initLoader);
                }
            }

            if (!self::$init) {
                self::$init = $initLoader;
            }

            return self::$init;
        } catch (\Throwable $e) {
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
    private static function isLoggedIn(): bool
    {
        return isset(getGlobalVariableData()['Auth']['Logged_In']) && getGlobalVariableData()['Auth']['Logged_In'];
    }

    /**
     * @throws \Throwable
     */
    public static function initAdminMenu(bool $dumpDB = true): void
    {
        \tree()->getTreeGenerator()->reset(new Node());

        event()->dispatch(new OnAdminMenu());
        $menuData = new MenuData();
        $tree = \tree()->getTreeGenerator()->getNodeTree();
        $menuID = $menuData->getCoreMenuID();
        $menuItems = []; $permissions = [];
        $mapper = [];

        foreach ($tree->getChildrenRecursive() as $node) {
            $parentID = null;
            if (!empty($node?->parentNode()->getSettings()['settings'])){
                $parentID = $node->parentNode()->getNameID();
            }

            if (isset($node->getSettings()['settings']['mt_url_slug'])){
                $mapper[$node->getSettings()['settings']['mt_url_slug']] = $node->getFullNodePath();
            }

            if (empty($node->getSettings()['settings']) || isset($node->getSettings()['settings']['ignore'])){
                $node->setNameID(null);
                continue;
            }

            if ($dumpDB){
                /**@var Node $node */
                $uuid = $node->uuid4();
                $settings = $node->getSettings()['settings'] ?? null;
                $menuItems[] = [
                    'fk_menu_id' => $menuID,
                    'mt_id' => $node->getNameID(),
                    'mt_parent_id' => $parentID,
                    'slug_id' => $uuid,
                    'mt_name' => $settings['mt_name'] ?? '',
                    'mt_icon' => $settings['mt_icon'] ?? '',
                    'mt_classes' => $settings['mt_classes'] ?? '',
                    'mt_target' => $settings['mt_target'] ?? '',
                    'mt_url_slug' => $settings['mt_url_slug'] ?? '',
                ];

                if (isset($settings['permission'])){
                    foreach ($settings['permission'] as $permission){
                        $permissions[] = [
                            'fk_menu_item_slug_id' => $uuid,
                            'fk_permission_id' => $permission,
                        ];
                    }
                }
            }

        }

        \tree()->getTreeGenerator()->setAnyData(['BreadCrumbMapper' => $mapper]);
        if ($dumpDB){
            db(onGetDB: function (TonicsQuery $db) use ($menuData, $menuID, $permissions, $menuItems) {
                $db->beginTransaction();
                # Delete All the Menu Items Related to $menuDetails->menuID
                $db->FastDelete($menuData->getMenuItemsTable(), db()->WhereEquals('fk_menu_id', $menuID));
                # Reinsert it
                $db->Insert($menuData->getMenuItemsTable(), $menuItems);
                # Insert Permissions
                if (!empty($permissions)){
                    $db->Insert($menuData->getMenuItemPermissionsTable(), $permissions);
                }
                $db->commit();
            });
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
        if (helper()->isCLI()) {
            return $route;
        }

        $pageTable = Tables::getTable(Tables::PAGES);
        try {
            $pages = null;
            db(onGetDB: function ($db) use ($pageTable, &$pages){
                $pages = $db->Select('*')->From($pageTable)->FetchResult();
            });
            foreach ($pages as $page) {
                if ($page->page_status === 1) {
                    # If url has not been chosen or is not a reserved path
                    $foundURLNode = $route->getRouteTreeGenerator()->findURL($page->page_slug);
                    if ($foundURLNode->getFoundURLNode() === null || empty($foundURLNode->getFoundURLNode()->getSettings())) {
                        $route->get($page->page_slug, [$controller, 'viewPage'], moreSettings: $page);
                    }
                }
            }
        } catch (Exception) {
            // log..
        }

        return $route;
    }

    public static function isMaintenanceMode(): bool
    {
        return (bool)env('MAINTENANCE_MODE', false);
    }

    /**
     * @throws Exception
     */
    public static function getTimeZone(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::AppSettings_AppTimeZone,  env('APP_TIME_ZONE', 'UTC'));
    }

    public static function getLanguage(): string
    {
        return env('APP_LANGUAGE', '');
    }

    /**
     * @throws Exception
     */
    public static function getAppName(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::AppSettings_AppName, env('APP_NAME', 'Tonics'));
    }

    /**
     * For throttling pagination on the frontend
     * @return string
     */
    public static function getAppInstalled(): string
    {
        return env('APP_INSTALLED', 1);
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
        return 'initLoader_' . env('APP_KEY', 'Tonics');
    }

    /**
     * TonicsIsReady means the tonics application has been installed (plus the db migrations has been generated),
     * we check by checking if getAppKey does not return xxx (xxx is the default when tonics env is newly uploaded).
     *
     * You might need to use this, to be sure you can use the database
     * @return bool
     */
    public static function TonicsIsReady(): bool
    {
        return AppConfig::getAppKey() !== 'xxx';
    }

    /**
     * The opposite of TonicsIsReady, it means Tonics is not installed, so, calling any db function or anything that relies on db is not safe.
     * Use this to check is Tonics is ready or not
     * @return bool
     */
    public static function TonicsIsNotReady(): bool
    {
        return AppConfig::getAppKey() === 'xxx';
    }


    public static function getAppKey(): string
    {
        return env('APP_KEY', 'Tonics');
    }

    /**
     * @throws Exception
     */
    public static function getAppEnv(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::AppSettings_AppEnvironment, env('APP_ENV', 'production'));
    }

    /**
     * @throws Exception
     */
    public static function getAppLog404(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::AppSettings_AppLog404, env('APP_LOG_404', '1'));
    }

    /**
     * @throws Exception
     */
    public static function isProduction(): bool
    {
        return AppConfig::getAppEnv() === 'production';
    }

    /**
     * @throws Exception
     */
    public static function canLog404(): bool
    {
        return self::getAppLog404() === '1';
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
     * @throws Exception
     */
    public static function getAutoUpdateApps(): array|bool
    {
        $update = CoreSettingsController::getSettingsValue(CoreSettingsController::Updates_AutoUpdateApps, env('AUTO_UPDATE_APPS', 'NULL'));
        return self::handleAutoUpdateReturn($update);
    }

    /**
     * If
     * - `true` then, all modules should be auto-updated.
     *  - false, nothing should be auto_updated.
     * - array, then only the array items should be auto_updated
     *
     * @return array|bool
     * @throws Exception
     */
    public static function getAutoUpdateModules(): array|bool
    {
        $update = CoreSettingsController::getSettingsValue(CoreSettingsController::Updates_AutoUpdateModules, env('AUTO_UPDATE_MODULES', 'NULL'));
        return self::handleAutoUpdateReturn($update);
    }

    public static function isActivateEventStreamMessage(): bool
    {
        return env('ACTIVATE_EVENT_STREAM_MESSAGE') === '1';
    }

    private static function handleAutoUpdateReturn($update): array|bool
    {
        if ($update === '0') {
            return false;
        }

        if ($update === '1') {
            return true;
        }
        $updates = explode(',', $update);
        if (is_array($updates) && !empty($updates)) {
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
        if (isset($updates->value) && !empty($updates->value)) {
            return json_decode($updates->value, true);
        }
        return [];
    }

    /**
     * @throws Exception
     */
    public static function getAppUrl(): string
    {
        return CoreSettingsController::getSettingsValue(CoreSettingsController::AppSettings_AppURL, env('APP_URL'));
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

    public static function getBinPath(): string
    {
        return APP_ROOT . '/bin';
    }

    public static function getBinRestartServiceJSONFile(): string
    {
        return APP_ROOT . '/bin/restart_service.json';
    }

    /**
     * This function should be called whenever an update, delete, installation of app and module is done.
     *
     * The timestamp change is just to trigger a modified_file_change in the restart_service, this way,
     * you can listen to that and restart any service running in bin/console
     * @return void
     * @throws Exception
     */
    public static function updateRestartService(): void
    {
        if (helper()->isFile(AppConfig::getBinRestartServiceJSONFile())){
            $json = file_get_contents(AppConfig::getBinRestartServiceJSONFile());
            if (helper()->isJSON($json)){
                $json = json_decode($json);
                if (isset($json->timestamp)){
                    $json->timestamp = time();
                }
               $result = @file_put_contents(AppConfig::getBinRestartServiceJSONFile(), json_encode($json));
                if ($result === true){
                    self::addUpdateMigrationsJob();
                }
            }
        }

    }

    /**
     * @return void
     * @throws Exception
     */
    public static function addUpdateMigrationsJob(): void
    {
        $updateMigration = new UpdateMigrations();
        $updateMigration->setJobName('UpdateMigrations');
        job()->enqueue($updateMigration);
    }

    public static function getAppAsset(string $appName, string $path): string
    {
        return DriveConfig::serveAppFilePath() . "$appName/?path=$path";
    }

    /**
     * @param string $moduleName
     * @param string $path
     * @return string
     */
    public static function getModuleAsset(string $moduleName, string $path): string
    {
        return DriveConfig::serveModuleFilePath() . "$moduleName/?path=$path";
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
        require AppConfig::getAppRoot() . '/src/Modules/Core/Boot/Helpers/all-helpers.php';
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
        if (is_object($object_or_class)) {
            $object_or_class = $object_or_class::class;
        }
        $moduleNameSpace = 'App\Apps';
        return str_starts_with($object_or_class, $moduleNameSpace);
    }


}