<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Database;
use App\Modules\Core\Library\MyPDO;
use App\Modules\Core\States\WordPressImportState;
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
    private Router $router;
    private TonicsView $tonicsView;
    private EventDispatcher $eventDispatcher;

    /**
     * Yh, Boot up the application
     * @throws Exception
     */
    public function BootDaBoot()
    {
        // dd(event()->dispatch(new OnAdminMenu())->generateMenuTree(), event());
        if (AppConfig::isMaintenanceMode()){
            die("Temporarily down for schedule maintenance, check back in few minutes");
        }

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
     * @return MyPDO
     * @throws Exception
     */
    public static function getDatabase(): MyPDO
    {
        return db();
    }

    /**
     * @return DomParser
     * @throws Exception
     */
    public function getDomParser(): DomParser
    {
        return dom();
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
     * @throws Exception
     */
    public function getSession(): Session
    {
        return \session();
    }


    /**
     * @return Container
     * @throws Exception
     */
    public function getContainer(): Container
    {
        return container();
    }

    /**
     * @return TonicsView
     */
    public function getTonicsView(): TonicsView
    {
        return $this->tonicsView;
    }

}