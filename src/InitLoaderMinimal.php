<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Database;
use App\Modules\Core\Library\MyPDO;
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
class InitLoaderMinimal
{
    private Container $container;
    private TonicsHelpers $tonicsHelpers;
    private Session $session;
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
    public function init()
    {
        self::addToGlobalVariable('App_Config', [
            'APP_NAME' => AppConfig::getAppName(),
            'APP_URL' => AppConfig::getAppUrl(),
            'APP_TIME_ZONE' => AppConfig::getTimeZone(),
            'APP_TIME_ZONE_OFFSET' => date('P'),
        ]);

        ## TimeZone
        date_default_timezone_set(AppConfig::getTimeZone());
                #-----------------------------------
            # INCLUDE THE HELPERS
        #-----------------------------------
        AppConfig::includeHelpers();
    }

    /**
     * @param Container $container
     * @return InitLoaderMinimal
     */
    public function setContainer(Container $container): InitLoaderMinimal
    {
        $this->container = $container;
        return $this;
    }


    /**
     * @param TonicsHelpers $tonicsHelpers
     * @return InitLoaderMinimal
     */
    public function setTonicsHelpers(TonicsHelpers $tonicsHelpers): InitLoaderMinimal
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
     * @return InitLoaderMinimal
     */
    public function setDomParser(DomParser $domParser): InitLoaderMinimal
    {
        $this->domParser = $domParser;
        return $this;
    }

    /**
     * @param Session $session
     * @return InitLoaderMinimal
     */
    public function setSession(Session $session): InitLoaderMinimal
    {
        $this->session = $session;
        return $this;
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

}