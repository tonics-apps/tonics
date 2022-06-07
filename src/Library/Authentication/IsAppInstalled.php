<?php

namespace App\Library\Authentication;

use App\Configs\AppConfig;
use App\Configs\DatabaseConfig;
use App\Library\Database;
use App\Library\SimpleState;
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
            $this->setErrorCode(self::ERROR_APP_ALREADY_INSTALLED__CODE)->setErrorMessage("Some DB Config Are Missing In Env File");
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
            $this->setErrorCode($e->getCode())->setErrorMessage("Can't Access The Database Server, Possible User/Pass Error");
            return self::ERROR;
        }
    }

    public function IsAppInstalledDatabaseMigrationStateHandler(): string
    {
        $db = (new Database())->createNewDatabaseInstance();

        $pdo = $db->getPdo();
        $stm = $pdo->prepare("SHOW TABLES");
        $stm->execute();

        $tablesInDatabase = $stm->fetchAll(\PDO::FETCH_COLUMN, 0);
        $requiredTables = AppConfig::requiredTables();

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