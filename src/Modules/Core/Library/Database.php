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

use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\DatabaseConfig;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsQueryBuilder\TonicsQueryBuilder;
use Devsrealm\TonicsQueryBuilder\Transformers\MariaDB\MariaDBTables;
use Devsrealm\TonicsQueryBuilder\Transformers\MariaDB\MariaDBTonicsQueryTransformer;
use PDO;

class Database
{

    protected array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * @param null $databaseName
     * @param null $Host
     * @param null $User
     * @param null $Pass
     * @param null $Char
     * @return TonicsQuery
     * @throws \Exception
     */
    public function createNewDatabaseInstance($databaseName = null, $Host = null, $User = null, $Pass =null, $Char = null): TonicsQuery
    {
        $dsn = 'mysql:host=' . ($this->Host() ?: $Host) .
            ';dbname=' . ($databaseName ?: $this->DatabaseName()) .
            ';charset=' . ($this->Charset() ?: $Char);

        try {

            $tonicsQueryBuilder = new TonicsQueryBuilder(new PDO($dsn, ($this->User() ?: $User), ($this->Password() ?: $Pass), options: $this->options),
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
        } catch (\PDOException $e){
            if (helper()->isCLI() === false){
                view('Modules::Core/Views/error-page', ['error-code' => $e->getCode(), 'error-message' => "Error Connecting To The Database ❕"]);
            }

            exit('Error Connecting To The Database' .  "\n" . $e->getMessage() . "\n");
        }

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