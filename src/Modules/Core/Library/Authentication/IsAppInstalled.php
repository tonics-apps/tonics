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

namespace App\Modules\Core\Library\Authentication;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Library\Database;
use App\Modules\Core\Library\SimpleState;
use PDOException;

/**
 * The IsInstallApp checks if the app is installed, if app is installed, you get SimpleState::DONE, else, SimpleState::ERROR,
 * you can also look into the reason the app installation failed by checking the SimpleState error properties
 */
class IsAppInstalled extends SimpleState
{
    # States For IsAppInstalled
    const IsAppInstalledInitialStateHandler = 'IsAppInstalledInitialStateHandler';
    const IsAppInstalledDatabaseConnectionStateHandler = 'IsAppInstalledDatabaseConnectionStateHandler';
    const IsAppInstalledDatabaseMigrationStateHandler = 'IsAppInstalledDatabaseMigrationStateHandler';

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        // Initial State
        $this->setCurrentState(self::IsAppInstalledInitialStateHandler);
        $this->runStates(false);
        return $this->getStateResult();
    }

    /**
     * @throws \Exception
     */
    public function IsAppInstalledInitialStateHandler(): string
    {

        if (helper()->isEmpty(
            [
                DatabaseConfig::getHost(), DatabaseConfig::getPort(), DatabaseConfig::getDatabase(),
                DatabaseConfig::getPrefix(), DatabaseConfig::getUsername(), DatabaseConfig::getPassword()
            ]
        )){
            $this->setErrorCode(self::ERROR_FORBIDDEN__CODE)->setErrorMessage("Some DB Config Are Missing In Env File");
            return self::ERROR;
        }

        $this->switchState(self::IsAppInstalledDatabaseConnectionStateHandler);
        return self::NEXT;
    }

    public function IsAppInstalledDatabaseConnectionStateHandler(): string
    {
        // Test Connection
        try {
            $dsn = 'mysql:host=' . DatabaseConfig::getHost() .
                ';dbname=' . DatabaseConfig::getDatabase() .
                ';charset=' . DatabaseConfig::getCharset();
            new \PDO($dsn, DatabaseConfig::getUsername(), DatabaseConfig::getPassword());
            $this->switchState(self::IsAppInstalledDatabaseMigrationStateHandler);
            return self::NEXT;
        } catch (PDOException $e) {
            # Would have used $e->getMessage() in the setErrorMessage, just wanna obscure the message a bit
            $this->setErrorCode(self::ERROR_FORBIDDEN__CODE)->setErrorMessage("Can't Access The Database Server, Possible User/Pass Error");
            return self::ERROR;
        }
    }

    /**
     * @throws \Exception
     */
    public function IsAppInstalledDatabaseMigrationStateHandler(): string
    {
        $db = (new Database())->createNewDatabaseInstance();

        $pdo = $db->getPdo();
        $stm = $pdo->prepare("SHOW TABLES");
        $stm->execute();

        $tablesInDatabase = $stm->fetchAll(\PDO::FETCH_COLUMN, 0);

        $modules = helper()->getModuleActivators([ExtensionConfig::class]);
        $requiredTables = [];
        foreach ($modules as $module) {
            $requiredTables = [...$requiredTables, ...array_keys($module->tables())];
        }

        $intersectionCount = count(array_intersect($tablesInDatabase, $requiredTables));

        # We have the required tables...meaning, app is installed
        if ($intersectionCount === count($requiredTables)){
            return self::DONE;
        }

        # We do not have the required tables...meaning, app installation is incomplete
        $this->setErrorCode(self::ERROR_APP_ALREADY_INSTALLED__CODE)->setErrorMessage("You do not have the required table to install the app");
        return self::ERROR;
    }

}