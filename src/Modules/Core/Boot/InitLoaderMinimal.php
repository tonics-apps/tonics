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

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Database;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsDomParser\DomParser;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
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

    private static TonicsQuery|null $db = null;

    private static array $globalVariable = [];

    /**
     * @param array $globalVariable
     */
    public static function setGlobalVariable(array $globalVariable): void
    {
        self::$globalVariable = $globalVariable;
    }

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
        ## TimeZone
        date_default_timezone_set(AppConfig::getTimeZone());

                #-----------------------------------
            # INCLUDE THE HELPERS
        #-----------------------------------
        AppConfig::includeHelpers();
        self::initGlobalVariables();
    }

    /**
     * @throws Exception
     */
    public static function initGlobalVariables(): void
    {
        self::addToGlobalVariable('App_Config', [
            'SiteURL' => AppConfig::getAppUrl(),
            'APP_NAME' => AppConfig::getAppName(),
            'APP_URL' => AppConfig::getAppUrl(),
            'APP_TIME_ZONE' => AppConfig::getTimeZone(),
            'APP_TIME_ZONE_OFFSET' => date('P'),
            'APP_ENV' => AppConfig::getAppEnv(),
            'isProduction' => AppConfig::isProduction(),
            'SERVE_APP_PATH' => DriveConfig::serveAppFilePath(),
            'SERVE_MODULE_PATH' => DriveConfig::serveModuleFilePath()
        ]);

        self::DRIVE_CONFIG_GlobalVariable();
        self::URL_GlobalVariable();
        $authInfo = UserData::getAuthenticationInfo();
        self::addToGlobalVariable('Auth', [
            'Logged_In' => !empty($authInfo?->role),
            'User_Role_Name' => $authInfo?->role,
            'User_Role_ID' => $authInfo?->role_id,
            'User_ID' => $authInfo?->user_id,
            'User_Email' => $authInfo?->email
        ]);

        # Push Structured Data That Relies on the Post Editor Here
        self::addToGlobalVariable('Structured_Data', [
            'FAQ' => []
        ]);
    }

    /**
     * @throws Exception
     */
    public static function noInstallationGlobalVariable(): void
    {
        self::DRIVE_CONFIG_GlobalVariable();
    }

    public static function URL_GlobalVariable(): void
    {
        url()->reset();
        self::addToGlobalVariable('URL', [
            'FULL_URL' => url()->getFullURL(),
            'REQUEST_URL' => url()->getRequestURL(),
            'PARAMS' => url()->getParams(),
            'REFERER' => url()->getReferer()
        ]);
    }

    public static function DRIVE_CONFIG_GlobalVariable(): void
    {
        self::addToGlobalVariable('Drive_Config', [
            'SERVE_APP_PATH' => DriveConfig::serveAppFilePath(),
            'SERVE_MODULE_PATH' => DriveConfig::serveModuleFilePath()
        ]);
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
     * @param bool $newConnection
     * @return TonicsQuery
     * @throws Exception
     */
    public static function getDatabase(bool $newConnection = false): TonicsQuery
    {
        if ($newConnection){
            self::$db = (new Database())->createNewDatabaseInstance();
        }

        if (!self::$db) {
            self::$db = (new Database())->createNewDatabaseInstance();
        }

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

    /**
     * @param $key
     * @return mixed
     */
    public static function getGlobalVariableData($key): mixed
    {
        if (isset(self::$globalVariable[$key])){
            return self::$globalVariable[$key];
        }

        return null;
    }

    /**
     * @param $key
     * @return bool
     */
    public static function globalVariableKeyExist($key): bool
    {
        return isset(self::$globalVariable[$key]);
    }

    /**
     * @param $key
     * @param $data
     * @return array
     */
    public static function addToGlobalVariable($key, $data): array
    {
        self::$globalVariable[$key] = $data;
        return self::$globalVariable;
    }

    public static function removeFromGlobalVariable($key): void
    {
        unset(self::$globalVariable[$key]);
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