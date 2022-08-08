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

use Exception;

class AbstractDataLayer
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected ?string $connection;

    /**
     * Database you wanna Interact With...
     * This would by default connect with the db in the config
     */
    protected MyPDO $DB;

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


    /**
     * @param string $table
     * @return mixed
     * @throws Exception
     */
    public function getTableCount(string $table): mixed
    {
        return db()->run("SELECT COUNT(*) AS 'r' FROM $table")[0]->r;
    }

    /**
     * @param string $searchTerm
     * @param string $table
     * @param string $colToSearch
     * @return mixed
     * @throws Exception
     */
    public function getSearchTableCount(string $searchTerm, string $table, string $colToSearch): mixed
    {
        return db()->run(<<<SQL
SELECT COUNT(*) AS 'r' FROM $table WHERE $colToSearch LIKE CONCAT('%', ?, '%')
SQL, $searchTerm)[0]->r;
    }

    /**
     * @param string $table
     * @param $offset
     * @param $limit
     * @param string|null $cols
     * @return mixed
     * @throws Exception
     */
    public function getRowWithOffsetLimit(string $table, $offset, $limit, string $cols = null): mixed
    {
        if ($cols !== null){
            return db()
                ->run("SELECT $cols FROM $table LIMIT ? OFFSET ?", $limit, $offset);
        }

        return db()
            ->run("SELECT * FROM $table LIMIT ? OFFSET ?", $limit, $offset);
    }

    /**
     * @param string $searchTerm
     * @param $offset
     * @param $limit
     * @param string $table
     * @param string $colToSearch
     * @param string|null $cols
     * @return mixed
     * @throws Exception
     */
    public function searchRowWithOffsetLimit(string $searchTerm, $offset, $limit, string $table, string $colToSearch, string $cols = null): mixed
    {

        if ($cols !== null){
            return db()->run(<<<SQL
SELECT $cols FROM $table WHERE $colToSearch LIKE CONCAT('%', ?, '%') LIMIT ? OFFSET ?
SQL, $searchTerm, $limit, $offset);
        }

        return db()->run(<<<SQL
SELECT * FROM $table WHERE $colToSearch LIKE CONCAT('%', ?, '%') LIMIT ? OFFSET ?
SQL, $searchTerm, $limit, $offset);
    }

    /**
     * Usage:
     * <br>
     * `$data->selectWithCondition('tablename', ['post_id', 'post_content'], "slug_id = ?", ['5475353']));`
     *
     * Note: Make sure you use a question-mark(?) in place u want a user input and pass the actual input in the $parameter
     * @param string $table
     * @param array $colToSelect
     * To select all, use ['*']
     * @param string $whereCondition
     * @param array $parameter
     * @param bool $singleRow
     * @return mixed
     * @throws \Exception
     */
    public function selectWithCondition(string $table, array $colToSelect, string $whereCondition, array $parameter, bool $singleRow = true): mixed
    {
        $select = helper()->returnDelimitedColumnsInBackTick($colToSelect);

        if ($colToSelect === ['*']){
            if ($singleRow){
                return db()->row(<<<SQL
SELECT * FROM $table WHERE $whereCondition
SQL, ...$parameter);
            }
            return db()->run(<<<SQL
SELECT * FROM $table WHERE $whereCondition
SQL, ...$parameter);
        }

        if ($singleRow){
            return db()->row(<<<SQL
SELECT $select FROM $table WHERE $whereCondition
SQL, ...$parameter);
        }

        return db()->run(<<<SQL
SELECT $select FROM $table WHERE $whereCondition
SQL, ...$parameter);
    }


    /**
     * @param array $data
     * @param array $whereCondition
     * @param string $table
     * @return int
     * @throws \Exception
     */
    public function updateWithCondition(array $data, array $whereCondition, string $table): int
    {
        return db()->update($table, $data, $whereCondition);
    }

    /**
     * Usage: `$newUserData->deleteWithCondition([], "slug_id = ?", ['php-dev'], 'posts_table');`
     * @param string $whereCondition
     * @param array $parameter
     * @param string $table
     * @throws \Exception
     */
    public function deleteWithCondition(string $whereCondition, array $parameter, string $table): void
    {
        db()->run(<<<SQL
DELETE FROM $table WHERE $whereCondition
SQL, ...$parameter);
    }

    /**
     * @return string|null
     */
    public function getConnection(): ?string
    {
        return $this->connection;
    }

    /**
     * The settings can have:
     *
     *  - query_name: Name of query in URL PARAM, use for search term
     *  - page_name: Name of page in URL PARAM, use for page size
     *  - per_page_name: Name to use for per_page in URL PARAM, use for the number of result to return in one go
     * @param string $cols
     * @param string $colToSearch
     * @param string $table
     * @param int $defaultPerPage
     * @param array $settings
     * @return object|null
     * @throws Exception
     */
    public function generatePaginationData(
        string $cols,
        string $colToSearch,
        string $table,
        int $defaultPerPage = 20,
    array $settings = []): ?object
    {
        $queryName = (isset($settings['query_name'])) ? $settings['query_name'] : 'query';
        $pageName = (isset($settings['page_name'])) ? $settings['page_name'] : 'page';
        $perPage = (isset($settings['per_page_name'])) ? $settings['per_page_name'] : 'per_page';
        // remove token query string:
        url()->removeParam("token");
        $searchQuery = url()->getParam($queryName, '');
        if ($searchQuery){
            $tableRows = $this->getSearchTableCount(
                $searchQuery,
                $table,
                $colToSearch);
        } else {
            $tableRows = $this->getTableCount($table);
        }

        return db()->paginate(
            tableRows: $tableRows,
            callback: function ($perPage, $offset) use ($colToSearch, $table, $cols, $searchQuery){
                if ($searchQuery){
                    return $this->searchRowWithOffsetLimit(
                        $searchQuery, $offset, $perPage,
                        $table, $colToSearch, $cols);
                } else {
                    return $this->getRowWithOffsetLimit($table, $offset, $perPage, $cols);
                }
            }, perPage: url()->getParam($perPage, $defaultPerPage), pageName: $pageName);
    }

    /**
     * @param string $table
     * @param array $columns
     * @param string $colParam
     * Col to use for parameters, e.g menu_id
     * @param callable $onSuccess
     * @param callable $onError
     * @param string $moreWhereCondition
     * e.g "AND data = 1"
     *
     * Specify where condition or uses $colParam if $whereCondition is empty
     * @return void
     * @throws Exception
     */
    public function deleteMultiple(
        string $table,
        array $columns,
        string $colParam,
        callable $onSuccess,
        callable $onError,
        string $moreWhereCondition = '')
    {
        $parameter = [];
        $itemsToDelete = array_map(function ($item) use ($colParam, $columns, &$parameter){
            $itemCopy = json_decode($item, true);
            $item = [];
            foreach ($itemCopy as $k => $v){
                if (key_exists($k, $columns)){
                    if ($k === $colParam){
                        $parameter[] = $v;
                    }
                    $item[$k] = $v;
                }
            }
            return $item;
        }, input()->fromPost()->retrieve('itemsToDelete'));

        try {
            $questionMarks = helper()->returnRequiredQuestionMarks([$itemsToDelete]);
            db()->run("DELETE FROM $table WHERE $colParam IN ($questionMarks) $moreWhereCondition", ...$parameter);
            $onSuccess();
        }catch (\Exception $e){
            $onError($e);
        }
    }
}