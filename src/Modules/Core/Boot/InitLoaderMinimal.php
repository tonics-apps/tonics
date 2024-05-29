<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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
    private static TonicsQuery|null $db             = null;
    private static array            $globalVariable = [];
    private Container               $container;
    private TonicsHelpers           $tonicsHelpers;
    private Session                 $session;
    private DomParser               $domParser; # Incomplete, but 95% usable for my use case.

    /**
     * Yh, Boot up the application
     * @throws Exception
     * @throws \Throwable
     */
    public function init ()
    {
        # TimeZone
        date_default_timezone_set(AppConfig::getTimeZone());

        # INCLUDE THE HELPERS
        AppConfig::includeHelpers();
        self::initGlobalVariables();
    }

    /**
     * @throws Exception
     * @throws \Throwable
     */
    public static function initGlobalVariables (): void
    {
        self::addToGlobalVariable('App_Config', [
            'SiteURL'              => AppConfig::getAppUrl(),
            'APP_NAME'             => AppConfig::getAppName(),
            'APP_URL'              => AppConfig::getAppUrl(),
            'APP_TIME_ZONE'        => AppConfig::getTimeZone(),
            'APP_TIME_ZONE_OFFSET' => date('P'),
            'APP_ENV'              => AppConfig::getAppEnv(),
            'isProduction'         => AppConfig::isProduction(),
            'SERVE_APP_PATH'       => DriveConfig::serveAppFilePath(),
            'SERVE_MODULE_PATH'    => DriveConfig::serveModuleFilePath(),
        ]);

        self::DRIVE_CONFIG_GlobalVariable();
        self::URL_GlobalVariable();
        $authInfo = UserData::getAuthenticationInfo();

        if (empty($authInfo)) {
            $authInfo = new \stdClass();
            $authInfo->role = false;
            $authInfo->role_name = null;
            $authInfo->role_id = null;
            $authInfo->user_id = null;
            $authInfo->email = null;
            $authInfo->user_table = null;
        }

        self::addToGlobalVariable('Auth', [
            'Logged_In'      => !empty($authInfo?->role),
            'User_Role_Name' => $authInfo?->role_name,
            'User_Role_ID'   => $authInfo?->role_id,
            'User_ID'        => $authInfo?->user_id,
            'User_Email'     => $authInfo?->email,
            'User_Table'     => $authInfo?->user_table,
        ]);

        # Push Structured Data That Relies on the Post Editor Here
        self::addToGlobalVariable('Structured_Data', [
            'FAQ' => [],
        ]);
    }

    /**
     * @throws \Throwable
     */
    public static function URL_GlobalVariable (): void
    {
        url()->reset();
        self::addToGlobalVariable('URL', [
            'FULL_URL'    => url()->getFullURL(),
            'REQUEST_URL' => url()->getRequestURL(),
            'PARAMS'      => url()->getParams(),
            'REFERER'     => url()->getReferer(),
        ]);
    }

    /**
     * @throws Exception
     */
    public static function noInstallationGlobalVariable (): void
    {
        self::DRIVE_CONFIG_GlobalVariable();
    }

    public static function DRIVE_CONFIG_GlobalVariable (): void
    {
        self::addToGlobalVariable('Drive_Config', [
            'SERVE_APP_PATH'    => DriveConfig::serveAppFilePath(),
            'SERVE_MODULE_PATH' => DriveConfig::serveModuleFilePath(),
        ]);
    }

    /**
     * @param $key
     * @param $data
     *
     * @return array
     */
    public static function addToGlobalVariable ($key, $data): array
    {
        self::$globalVariable[$key] = $data;
        return self::$globalVariable;
    }

    /**
     * @param bool $newConnection
     *
     * @return TonicsQuery
     * @throws Exception
     */
    public static function getDatabase (bool $newConnection = false): TonicsQuery
    {
        if ($newConnection) {
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
    public static function getGlobalVariable (): array
    {
        if (!self::$globalVariable) {
            self::$globalVariable = [];
        }
        return self::$globalVariable;
    }

    /**
     * @param array $globalVariable
     */
    public static function setGlobalVariable (array $globalVariable): void
    {
        self::$globalVariable = $globalVariable;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function getGlobalVariableData ($key): mixed
    {
        if (isset(self::$globalVariable[$key])) {
            return self::$globalVariable[$key];
        }

        return null;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public static function globalVariableKeyExist ($key): bool
    {
        return isset(self::$globalVariable[$key]);
    }

    public static function removeFromGlobalVariable ($key): void
    {
        unset(self::$globalVariable[$key]);
    }

    /**
     * @return TonicsHelpers
     */
    public function getTonicsHelpers (): TonicsHelpers
    {
        return $this->tonicsHelpers;
    }

    /**
     * @param TonicsHelpers $tonicsHelpers
     *
     * @return InitLoaderMinimal
     */
    public function setTonicsHelpers (TonicsHelpers $tonicsHelpers): InitLoaderMinimal
    {
        $this->tonicsHelpers = $tonicsHelpers;
        return $this;
    }

    /**
     * @return DomParser
     */
    public function getDomParser (): DomParser
    {
        return $this->domParser;
    }

    /**
     * @param DomParser $domParser
     *
     * @return InitLoaderMinimal
     */
    public function setDomParser (DomParser $domParser): InitLoaderMinimal
    {
        $this->domParser = $domParser;
        return $this;
    }

    /**
     * @return Session
     */
    public function getSession (): Session
    {
        return $this->session;
    }

    /**
     * @param Session $session
     *
     * @return InitLoaderMinimal
     */
    public function setSession (Session $session): InitLoaderMinimal
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer (): Container
    {
        return $this->container;
    }

    /**
     * @param Container $container
     *
     * @return InitLoaderMinimal
     */
    public function setContainer (Container $container): InitLoaderMinimal
    {
        $this->container = $container;
        return $this;
    }

}