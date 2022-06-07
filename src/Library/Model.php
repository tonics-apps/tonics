<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Library;


use ParagonIE\EasyDB\EasyDB;

abstract class Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected ?string $connection;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected string $tableName;

    /**
     * Database you wanna Interact With...
     * This would by default connect with the db in the config
     */
    protected EasyDB $DB;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected string $primaryKey = 'id';


    /**
     * An array of columns in the table
     * @var array
     */
    protected array $columns;

    /**
     * The number of rows to return per page, useful for pagination.
     *
     * @var int
     */
    protected int $rowsPerPage = 15;
    
    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = 'updated_at';

    public function __construct(Database $DB){
       $this->DB = $DB->createNewDatabaseInstance();
    }

    /**
     * @return MyPDO|EasyDB
     */
    public function getDB(): MyPDO|EasyDB
    {
        return db();
    }

    public function getAllRecords(){
        return $this->getDB()->run("SELECT * FROM {$this->getTableName()}");
    }

    public function getRecordsByKey($id){

        return $this->getDB()->row(
            "SELECT * FROM {$this->getTableName()} WHERE {$this->getPrimaryKey()} = ?",
            $id
        );

    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return string|null
     */
    public function getConnection(): ?string
    {
        return $this->connection;
    }


    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return int
     */
    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }
}