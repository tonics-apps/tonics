<?php
/*
 *     Copyright (c) 2022-2025. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Boot;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
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
    private static ?Job $jobEventDispatcher = null;
    private static ?Scheduler $scheduler = null;
    private static bool $eventStreamAsHTML = false;
    private Router $router;
    private TonicsView $tonicsView;
    private TonicsTemplateEngines $tonicsTemplateEngines;
    private EventDispatcher $eventDispatcher;

    /**
     * @return bool
     */
    public static function isEventStreamAsHTML(): bool
    {
        return self::$eventStreamAsHTML;
    }

    /**
     * If set to true, a br tag would be appended to every sent event stream message
     *
     * @param bool $eventStreamAsHTML
     */
    public static function setEventStreamAsHTML(bool $eventStreamAsHTML): void
    {
        self::$eventStreamAsHTML = $eventStreamAsHTML;
    }

    /**
     * @throws Exception
     */
    public static function getAllApps(): array
    {
        return helper()->getModuleActivators([ExtensionConfig::class], helper()->getAllAppsDirectory());
    }

    /**
     * @param string $transporterName
     *
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
     *
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
     * Yh, Boot up the application
     * @throws Exception
     * @throws \Throwable
     */
    public function BootDaBoot(): void
    {
        if (AppConfig::isMaintenanceMode()) {
            die("Temporarily down for schedule maintenance, check back in few minutes");
        }

        #-----------------------------------
        # HEADERS SETTINGS TEST
        #-----------------------------------
        if (AppConfig::TonicsIsReady()) {
            response()->headers([
                'Access-Control-Allow-Origin: ' . AppConfig::getAppUrl(),
                'Access-Control-Allow-Credentials: true',
                'Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, Authorization',
                'X-Content-Type-Options: nosniff',
                'X-Frame-Options: SAMEORIGIN',
                'Referrer-Policy: strict-origin-when-cross-origin',
                'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload',
                'Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()',
            ]);
        }

        #----------------------------------------------------
        # GATHER ROUTES AND PREPARE FOR PROCESSING
        #---------------------------------------------------
        request()->reset();
        foreach ($this->Providers() as $provider) {
            $this->getContainer()->register($provider);
        }
    }

    /**
     * @return HttpMessageProvider[]
     * @throws Exception
     */
    protected function Providers(): array
    {
        return [
            new HttpMessageProvider(
                $this->router,
            ),
        ];
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
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcher $eventDispatcher
     *
     * @return InitLoader
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): InitLoader
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
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
     *
     * @return InitLoader
     */
    public function setTonicsTemplateEngines(TonicsTemplateEngines $tonicsTemplateEngines): InitLoader
    {
        $this->tonicsTemplateEngines = $tonicsTemplateEngines;
        return $this;
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
     * @return TonicsView
     */
    public function getTonicsView(): TonicsView
    {
        return $this->tonicsView;
    }

    /**
     * @param TonicsView $tonicsView
     *
     * @return InitLoader
     */
    public function setTonicsView(TonicsView $tonicsView): InitLoader
    {
        $this->tonicsView = $tonicsView;
        return $this;
    }

    /**
     * Register the route for the module
     *
     * @param ExtensionConfig $module
     *
     * @return Route
     */
    protected function registerRoutes(ExtensionConfig $module): Route
    {
        return $module->route($this->getRouter()->getRoute());
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * @param Router $router
     *
     * @return InitLoader
     */
    public function setRouter(Router $router): InitLoader
    {
        $this->router = $router;
        return $this;
    }

}