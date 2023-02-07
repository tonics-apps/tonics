<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsQueryBuilder\TonicsQueryBuilder;
use Devsrealm\TonicsQueryBuilder\Transformers\MariaDB\MariaDBTables;
use Devsrealm\TonicsQueryBuilder\Transformers\MariaDB\MariaDBTonicsQueryTransformer;
use PDO;

class Database
{
    use ConsoleColor;

    protected array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    /**
     * @param array $settings
     * @return TonicsQuery
     * @throws \Exception
     */
    public function createNewDatabaseInstance(array $settings = []): TonicsQuery
    {
        $counter = 20;
        $exc = null;
        while ($counter > 0) {
            try {
                return $this->getConnection($settings);
            } catch (\PDOException $e){
                $exc = $e;
                $counter--;
                if (helper()->isCLI()){
                    sleep(1);
                    $this->infoMessage("Error Connecting To The Database [{$exc->getMessage()}], Retrying To Connect, Retry Left $counter \n");
                }
            }
        }
        if (helper()->isCLI() === false){
            view('Modules::Core/Views/error-page', ['error-code' => $exc->getCode(), 'error-message' => "Error Connecting To The Database ❕"]);
        }
        exit('Error Connecting To The Database' .  "\n" . $exc->getMessage() . "\n");

    }

    /**
     * @param array $settings
     * @return TonicsQuery
     * @throws \Exception
     */
    private function getConnection(array $settings = []): TonicsQuery
    {
        $tonicsQueryBuilder = new TonicsQueryBuilder($this->getInstanceOfPdoObj(),
            new MariaDBTonicsQueryTransformer(),
            new MariaDBTables(''));

        $q = $tonicsQueryBuilder->getTonicsQuery();
        $t = $tonicsQueryBuilder->getTables();

        # Sync Users TimeZone with Database
        $offset = date('P');
        $q->Q()->Set('time_zone', $offset)->FetchFirst();
        # Set Up Tables
        $modules = helper()->getModuleActivators([ExtensionConfig::class]);
        $apps = helper()->getModuleActivators([ExtensionConfig::class], helper()->getAllAppsDirectory());
        $modules = [...$modules, ...$apps];
        foreach ($modules as $module) {
            foreach ($module->tables() as $tableName => $tableValues){
                $t->addTable($tableName, $tableValues);
            }
        }

        return $q;
    }

    public function getInstanceOfPdoObj(): PDO
    {
        $databaseName = $settings['databaseName'] ?? null;
        $Host = $settings['host'] ?? null;
        $User = $settings['user'] ?? null;
        $Pass = $settings['pass'] ?? null;
        $Char = $settings['char'] ?? null;

        $dsn = 'mysql:host=' . ($this->Host() ?: $Host) .
            ';dbname=' . ($databaseName ?: $this->DatabaseName()) .
            ';charset=' . ($this->Charset() ?: $Char);

        return new PDO($dsn, ($this->User() ?: $User), ($this->Password() ?: $Pass), options: $this->options);
    }

    /**
     * @return bool|array|string
     */
    private function DatabaseName(): bool|array|string
    {
        return env('DB_DATABASE');
    }

    /**
     * @return bool|array|string
     */
    private function Charset(): bool|array|string
    {
        return env('DB_CHARSET');
    }

    /**
     * @return bool|array|string
     */
    private function Host(): bool|array|string
    {
        return env('DB_HOST');
    }

    /**
     * @return bool|array|string
     */
    private function User(): bool|array|string
    {
        return env('DB_USERNAME');
    }

    /**
     * @return bool|array|string
     */
    private function Password(): bool|array|string
    {
        return env('DB_PASSWORD');
    }

    /**
     * @return bool|array|string
     */
    private function Engine(): bool|array|string
    {
        return env('DB_ENGINE');
    }

}