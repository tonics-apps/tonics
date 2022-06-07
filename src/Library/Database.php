<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Library;


use ParagonIE\EasyDB\EasyDB;
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
     * @return MyPDO
     * @throws \Exception
     */
    public function createNewDatabaseInstance($databaseName = null, $Host = null, $User = null, $Pass =null, $Char = null): EasyDB
    {
        $dsn = 'mysql:host=' . ($this->Host() ?: $Host) .
            ';dbname=' . ($databaseName ?: $this->DatabaseName()) .
            ';charset=' . ($this->Charset() ?: $Char);


        try {
            return new MyPDO(new PDO(
                dsn: $dsn,
                username: ($this->User() ?: $User),
                password: ($this->Password() ?: $Pass)), dbEngine: $this->Engine(), options: $this->options);
        } catch (\PDOException $e){
            view('Modules::Core/Views/error-page', ['error-code' => $e->getCode(), 'error-message' => "Error Connecting To The Database ❕"]);
            exit();
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