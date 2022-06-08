<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App;

use App\Configs\AppConfig;
use App\Library\Authentication\Session;
use App\Library\Database;
use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\MyPDO;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsDomParser\DomParser;
use Devsrealm\TonicsEventSystem\EventDispatcher;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsRouterSystem\Handler\Router;
use Devsrealm\TonicsRouterSystem\Route;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use Exception;

/**
 * The initial loader of the app
 * Class InitLoader
 */
class InitLoader
{
    private Container $container;
    private TonicsHelpers $tonicsHelpers;
    private Router $router;
    private TonicsView $tonicsView;
    private Session $session;
    private EventDispatcher $eventDispatcher;
    # Incomplete, but 95% usable for my use case.
    private DomParser $domParser;

    private static MyPDO|null $db = null;

    private static array $globalVariable = [];

    /**
     * @return TonicsHelpers
     */
    public function getTonicsHelpers(): TonicsHelpers
    {
        return $this->tonicsHelpers;
    }

    /**
     * Yh, Boot up the application
     * @throws Exception
     */
    public function BootDaBoot()
    {
                #-----------------------------------
            # INCLUDE THE HELPERS
        #-----------------------------------
        AppConfig::includeHelpers();
        // dd(event()->dispatch(new OnAdminMenu())->generateMenuTree(), event());

        ## TimeZone
        date_default_timezone_set(AppConfig::getTimeZone());
        // dd(InitLoader::getAllThemes(), InitLoader::getAllPlugins());

                #-----------------------------------
            # HEADERS SETTINGS TEST
        #-----------------------------------
        response()->headers([
            'Access-Control-Allow-Origin: ' . AppConfig::getAppUrl(),
            'Access-Control-Allow-Credentials: true',
            'Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, Authorization',
        ]);

                #----------------------------------------------------
            # GATHER ROUTES AND PREPARE FOR PROCESSING
        #---------------------------------------------------
        request()->reset();
        foreach ($this->Providers() as $provider) {
            $this->getContainer()->register($provider);
        }
    }

    /**
     * @throws Exception
     */
    public static function getAllThemes(): array
    {
        return helper()->getModuleActivators([ModuleConfig::class], helper()->getAllThemesDirectory());
    }

    /**
     * @throws Exception
     */
    public static function getAllPlugins(): array
    {
        return helper()->getModuleActivators([ModuleConfig::class], helper()->getAllPluginsDirectory());
    }

    /**
     * @return HttpMessageProvider[]
     * @throws Exception
     */
    protected function Providers(): array
    {
        return [
            new HttpMessageProvider(
                $this->router
            )
        ];
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @param Container $container
     * @return InitLoader
     */
    public function setContainer(Container $container): InitLoader
    {
        $this->container = $container;
        return $this;
    }


    /**
     * @param TonicsHelpers $tonicsHelpers
     * @return InitLoader
     */
    public function setTonicsHelpers(TonicsHelpers $tonicsHelpers): InitLoader
    {
        $this->tonicsHelpers = $tonicsHelpers;
        return $this;
    }

    /**
     * @return MyPDO
     * @throws Exception
     */
    public static function getDatabase(): MyPDO
    {
        if (!self::$db) {
            self::$db = (new Database())->createNewDatabaseInstance();
        }

        self::$db->setDbEngine('mysql');
        return self::$db;
    }

    /**
     * @throws Exception
     */
    public static function getGlobalVariable(): array
    {
        if (!self::$globalVariable) {
            self::$globalVariable = [];
        }
        return self::$globalVariable;
    }

    public static function addToGlobalVariable($key, $data): array
    {
        self::$globalVariable[$key] = $data;
        return self::$globalVariable;
    }

    /**
     * @return DomParser
     */
    public function getDomParser(): DomParser
    {
        return $this->domParser;
    }

    /**
     * @param DomParser $domParser
     * @return InitLoader
     */
    public function setDomParser(DomParser $domParser): InitLoader
    {
        $this->domParser = $domParser;
        return $this;
    }

    /**
     * @param Router $router
     * @return InitLoader
     */
    public function setRouter(Router $router): InitLoader
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @param TonicsView $tonicsView
     * @return InitLoader
     */
    public function setTonicsView(TonicsView $tonicsView): InitLoader
    {
        $this->tonicsView = $tonicsView;
        return $this;
    }

    /**
     * @param Session $session
     * @return InitLoader
     */
    public function setSession(Session $session): InitLoader
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @param EventDispatcher $eventDispatcher
     * @return InitLoader
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): InitLoader
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Register the route for the module
     *
     * @param ModuleConfig $module
     * @return Route
     */
    protected function registerRoutes(ModuleConfig $module): Route
    {
        return $module->route($this->getRouter()->getRoute());
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }


    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return TonicsView
     */
    public function getTonicsView(): TonicsView
    {
        return $this->tonicsView;
    }

}