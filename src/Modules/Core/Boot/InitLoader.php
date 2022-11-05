<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Boot;

use App\Library;
use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsDomParser\DomParser;
use Devsrealm\TonicsEventSystem\EventDispatcher;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
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
    private TonicsTemplateEngines $tonicsTemplateEngines;
    private EventDispatcher $eventDispatcher;
    private static ?Job $jobEventDispatcher = null;
    private static ?Scheduler $scheduler = null;

    private static bool $eventStreamAsHTML = false;

    /**
     * @return bool
     */
    public static function isEventStreamAsHTML(): bool
    {
        return self::$eventStreamAsHTML;
    }

    /**
     * If set to true, a br tag would be appended to every sent event stream message
     * @param bool $eventStreamAsHTML
     */
    public static function setEventStreamAsHTML(bool $eventStreamAsHTML): void
    {
        self::$eventStreamAsHTML = $eventStreamAsHTML;
    }

    /**
     * Yh, Boot up the application
     * @throws Exception
     */
    public function BootDaBoot()
    {
        if (AppConfig::isMaintenanceMode()){
            die("Temporarily down for schedule maintenance, check back in few minutes");
        }

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
    public static function getAllApps(): array
    {
        return helper()->getModuleActivators([ExtensionConfig::class], helper()->getAllAppsDirectory());
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
     * @param string $transporterName
     * @return Job
     * @throws Exception
     */
    public static function getJobEventDispatcher(string $transporterName): Job
    {
        if (!self::$jobEventDispatcher) {
            self::$jobEventDispatcher = new Job($transporterName);
        }
        self::$jobEventDispatcher->setTransporterName($transporterName);
        return self::$jobEventDispatcher;
    }

    /**
     * @param string $transporterName
     * @return Scheduler
     * @throws Exception
     */
    public static function getScheduler(string $transporterName): Scheduler
    {
        if (!self::$scheduler) {
            self::$scheduler = new Scheduler($transporterName);
        }
        self::$scheduler->setTransporterName($transporterName);
        return self::$scheduler;
    }

    /**
     * @return TonicsQuery
     * @throws Exception
     */
    public static function getDatabase(): TonicsQuery
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
     * @return TonicsTemplateEngines
     */
    public function getTonicsTemplateEngines(): TonicsTemplateEngines
    {
        return $this->tonicsTemplateEngines;
    }

    /**
     * @param TonicsTemplateEngines $tonicsTemplateEngines
     * @return InitLoader
     */
    public function setTonicsTemplateEngines(TonicsTemplateEngines $tonicsTemplateEngines): InitLoader
    {
        $this->tonicsTemplateEngines = $tonicsTemplateEngines;
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
     * @param Library\ModuleRegistrar\Interfaces\ExtensionConfig $module
     * @return Route
     */
    protected function registerRoutes(Library\ModuleRegistrar\Interfaces\ExtensionConfig $module): Route
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