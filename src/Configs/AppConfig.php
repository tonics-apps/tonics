<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Configs;


use App\InitLoader;
use App\Library\Authentication\Session;
use App\Library\Database;
use App\Library\ModuleRegistrar\Interfaces\ModuleConfig as ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Library\Router\RouteResolver;
use App\Library\Tables;
use App\Library\View\Extensions\CombineModeHandler;
use App\Library\View\Extensions\CSRFModeHandler;
use App\Library\View\Extensions\Events;
use App\Library\View\Extensions\IfBlock;
use App\Library\View\Extensions\IfCondition;
use App\Library\View\Extensions\MenuModeHandler;
use App\Library\View\Extensions\ModuleFunctionModeHandler;
use App\Library\View\Extensions\SessionView;
use App\Library\View\Extensions\URLModeHandler;
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
use Devsrealm\TonicsTemplateSystem\Caching\TonicsTemplateApcuCache;
use Devsrealm\TonicsTemplateSystem\Content;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateFileLoader;
use Devsrealm\TonicsTemplateSystem\Tokenizer\State\DefaultTokenizerState;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AppConfig
{
    private static InitLoader|null $init = null;

    /**
     * The very first entry point into our app, this uses injection sort of to construct all the
     * necessary objects, and caches it, so, it constructs it just once, and the subsequent request might be a bit faster.
     * @param bool $failSilently
     * @return InitLoader
     * @throws Exception
     */
    public static function initLoader(bool $failSilently = false): InitLoader
    {
        try {
            $initKey = self::getAppCacheKey();
            if (function_exists('apcu_enabled') && apcu_exists($initKey)){
                $initLoader = apcu_fetch($initKey);
              //  dd($initLoader);
            } else {
                ## Tonics View
                $templateLoader = new TonicsTemplateFileLoader('html');
                $templateLoader->resolveTemplateFiles(AppConfig::getModulesPath());
                $templateLoader->resolveTemplateFiles(AppConfig::getPluginsPath());
                $templateLoader->resolveTemplateFiles(AppConfig::getThemesPath());
                $settings = [
                    'templateLoader' => $templateLoader,
                    'tokenizerState' => new DefaultTokenizerState(),
                    'templateCache' => new TonicsTemplateApcuCache(),
                    'content' => new Content()
                ];
                $view = new TonicsView($settings);
                $view->addModeHandler('url', URLModeHandler::class);
                $view->addModeHandler('csrf', CSRFModeHandler::class);
                $view->addModeHandler('menu', MenuModeHandler::class);
                $view->addModeHandler('combine', CombineModeHandler::class);
                $view->addModeHandler('mFunc', ModuleFunctionModeHandler::class);

                $view->addModeHandler('session', SessionView::class);
                $view->addModeHandler('__session', SessionView::class);

                $view->addModeHandler('event', Events::class);
                $view->addModeHandler('__event', Events::class);

                $view->addModeHandler('if', IfCondition::class);
                $view->addModeHandler('ifBlock', IfBlock::class);

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

                ## Tonics Helper, Setting Up Events, Modules, and Plugins
                $tonicsHelper = new TonicsHelpers();
                $tonicsHelper->setModulesPath(AppConfig::getModulesPath());
                $tonicsHelper->setPluginsPath(AppConfig::getPluginsPath());
                $tonicsHelper->setThemesPath(AppConfig::getThemesPath());
                $modules = $tonicsHelper->getModuleActivators([ModuleConfig::class]);
                $plugins = $tonicsHelper->getPluginActivators([ModuleConfig::class, PluginConfig::class]);
                $theme = $tonicsHelper->getPluginActivators([ModuleConfig::class], $tonicsHelper->getAllThemesDirectory());

                if (!empty($theme)){
                    $theme = $theme[array_key_first($theme)];
                }

                $events = [];
                foreach ($modules as $module) {
                    /** @var $module ModuleConfig  */
                    // you can disable each module in its own config
                    // This gives us the module availability
                    if ($module->enabled()) {
                        // $events = [...$events, ...$module->events()];
                        $events = array_merge_recursive($events, $module->events());
                        // append the Routes by chaining...
                        $module->route($router->getRoute());
                    }
                }

                ## Plugins Would Only Appear if they have .installed (which would be added programmatically on installation)
                foreach ($plugins as $plugin){
                    /** @var $plugin ModuleConfig|PluginConfig  */
                    if ($plugin->enabled()){
                        $plugin->route($router->getRoute());
                        ## The array_intersect_key checks if the plugin event array has something in common with the module event($events),
                        # so, I just recursively merge only the intersection (using recursive merging because you might have several events in your modules).
                        $events = array_merge_recursive($events, array_intersect_key($plugin->events(), $events));
                    }
                }

                ## Unlike Plugins, You Can Only Have One Theme.
                /** @var $theme ModuleConfig|PluginConfig  */
                if ($theme instanceof ModuleConfig){
                    if ($theme->enabled()){
                        $theme->route($router->getRoute());
                        ## The array_intersect_key checks if the plugin event array has something in common with the module event($events),
                        # so, I just recursively merge only the intersection (using recursive merging because you might have several events in your modules).
                        $events = array_merge_recursive($events, array_intersect_key($theme->events(), $events));
                    }
                }

                $eventQueue = new EventQueue();
                $eventDispatcher = new EventDispatcher($eventQueue->addMultipleEventsAndHandlers($events));
                ## Construct The GrandFather...
                $initLoader = new InitLoader();
                $initLoader
                    ->setContainer(new Container())
                    ->setRouter($router)
                    ->setTonicsHelpers($tonicsHelper)
                    ->setTonicsView($view)
                    ->setSession(new Session())
                    ->setEventDispatcher($eventDispatcher)
                    ->setDomParser(new DomParser());
                if (function_exists('apcu_enabled')){
                    apcu_store($initKey, $initLoader);
                }
            }
            if (!self::$init) {
                self::$init = $initLoader;
            }

            return self::$init;
        } catch (Exception $e) {
            if ($failSilently){
                ## Fail Silently
                exit(1);
            }
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public static function autoResolvePageRoutes(string $controller, Route $route)
    {
        $db = (new Database())->createNewDatabaseInstance();
        $pageTable = Tables::getTable(Tables::PAGES);
        $pages = $db->run("SELECT * FROM $pageTable");
        foreach ($pages as $page){
            if ($page->page_status === 1){
                # e.g. page_slug with posts, would be viewPosts
                $route->get($page->page_slug, [$controller, 'viewPage'], moreSettings: $page);
            }
        }
        return $route;
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

    public static function getAppCacheKey(): string
    {
        return  'initLoader_'.env('APP_NAME', 'Tonics');
    }

    public static function getAppEnv(): string
    {
        return env('APP_ENV', 'production');
    }

    public static function getAppUpdateKey(): string
    {
        return env('UPDATE_KEY', 'NULL');
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

    public static function getPluginsPath(): string
    {
        return APP_ROOT . '/src/Plugins';
    }

    public static function getThemesPath(): string
    {
        return APP_ROOT . '/src/Themes';
    }

    public static function getThemesAsset(string $themeName, string $themePath): string
    {
        return "/assets/themes/$themeName/?path=$themePath";
    }

    public static function getPluginAsset(string $pluginName, string $pluginPath): string
    {
        return "/assets/plugins/$pluginName/?path=$pluginPath";
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

    /**
     * Required tables for the app to function
     * @return array
     */
    public static function requiredTables(): array
    {
        return [
            # Core
            Tables::getTable(Tables::ADMINS) => Tables::getTable(Tables::ADMINS),
            Tables::getTable(Tables::PLUGINS) => Tables::getTable(Tables::PLUGINS),
            Tables::getTable(Tables::SESSIONS) => Tables::getTable(Tables::SESSIONS),
            Tables::getTable(Tables::GLOBAL) => Tables::getTable(Tables::GLOBAL),
            Tables::getTable(Tables::USER_TYPE) => Tables::getTable(Tables::USER_TYPE),
            Tables::getTable(Tables::USERS) => Tables::getTable(Tables::USERS),

            # Customer
            Tables::getTable(Tables::CUSTOMERS) => Tables::getTable(Tables::CUSTOMERS),

            # Media
            Tables::getTable(Tables::DRIVE_BLOB_COLLATOR) => Tables::getTable(Tables::DRIVE_BLOB_COLLATOR),
            Tables::getTable(Tables::DRIVE_SYSTEM) => Tables::getTable(Tables::DRIVE_SYSTEM),

            # Menus
            Tables::getTable(Tables::MENU_ITEMS) => Tables::getTable(Tables::MENU_ITEMS),
            Tables::getTable(Tables::MENU_LOCATIONS) => Tables::getTable(Tables::MENU_LOCATIONS),
            Tables::getTable(Tables::MENUS) => Tables::getTable(Tables::MENUS),

            # Pages
            Tables::getTable(Tables::PAGES) => Tables::getTable(Tables::PAGES),

            # Posts
            Tables::getTable(Tables::CATEGORIES) => Tables::getTable(Tables::CATEGORIES),
            Tables::getTable(Tables::CAT_RELS) => Tables::getTable(Tables::CAT_RELS),
            Tables::getTable(Tables::POSTS) => Tables::getTable(Tables::POSTS),
            Tables::getTable(Tables::POST_CATEGORIES) => Tables::getTable(Tables::POST_CATEGORIES),
            Tables::getTable(Tables::TAGS) => Tables::getTable(Tables::TAGS),
            Tables::getTable(Tables::TAG_RELS) => Tables::getTable(Tables::TAG_RELS),

            # Tracks
            Tables::getTable(Tables::ARTISTS) => Tables::getTable(Tables::ARTISTS),
            Tables::getTable(Tables::GENRES) => Tables::getTable(Tables::GENRES),
            Tables::getTable(Tables::LICENSES) => Tables::getTable(Tables::LICENSES),
            Tables::getTable(Tables::PURCHASES) => Tables::getTable(Tables::PURCHASES),
            Tables::getTable(Tables::PURCHASE_TRACKS) => Tables::getTable(Tables::PURCHASE_TRACKS),
            Tables::getTable(Tables::TRACKS) => Tables::getTable(Tables::TRACKS),
            Tables::getTable(Tables::TRACK_LIKES) => Tables::getTable(Tables::TRACK_LIKES),
            Tables::getTable(Tables::WISH_LIST) => Tables::getTable(Tables::WISH_LIST),

            # Widgets
            Tables::getTable(Tables::WIDGET_LOCATIONS) => Tables::getTable(Tables::WIDGET_LOCATIONS),
            Tables::getTable(Tables::WIDGETS) => Tables::getTable(Tables::WIDGETS),
            Tables::getTable(Tables::WIDGET_ITEMS) => Tables::getTable(Tables::WIDGET_ITEMS),

            # Fields
            Tables::getTable(Tables::FIELD) => Tables::getTable(Tables::FIELD),
            Tables::getTable(Tables::FIELD_ITEMS) => Tables::getTable(Tables::FIELD_ITEMS),
        ];
    }

}