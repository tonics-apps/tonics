<?php

namespace App\Library;

use App\Library\Authentication\Session;
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
     * @param callable|null $customTableCount
     * @return mixed
     * @throws \Exception
     */
    public function getTableCount(string $table, callable $customTableCount = null): mixed
    {
        if ($customTableCount !== null){
            return $customTableCount($table);
        }
        return db()->run("SELECT COUNT(*) AS 'r' FROM $table")[0]->r;
    }

    /**
     * @param string $searchTerm
     * @param string $table
     * @param string $colToSearch
     * @param callable|null $customSearchTableCount
     * @return mixed
     * @throws \Exception
     */
    public function getSearchTableCount(string $searchTerm, string $table, string $colToSearch, callable $customSearchTableCount = null): mixed
    {
        if ($customSearchTableCount !== null){
            return $customSearchTableCount($table, $searchTerm, $colToSearch);
        }
        return db()->run(<<<SQL
SELECT COUNT(*) AS 'r' FROM $table WHERE $colToSearch LIKE CONCAT('%', ?, '%')
SQL, $searchTerm)[0]->r;
    }

    /**
     * @param string $table
     * @param $offset
     * @param $limit
     * @param string|null $cols
     * @param callable|null $customGetRowWithOffsetLimit
     * @return mixed
     * @throws Exception
     */
    public function getRowWithOffsetLimit(string $table, $offset, $limit, string $cols = null, callable $customGetRowWithOffsetLimit = null): mixed
    {
        if ($customGetRowWithOffsetLimit !== null){
            return $customGetRowWithOffsetLimit($table, $offset, $limit,  $cols);
        }

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
     * @param callable|null $customSearchRowWithOffsetLimit
     * @return mixed
     * @throws Exception
     */
    public function searchRowWithOffsetLimit(string $searchTerm, $offset, $limit, string $table, string $colToSearch, string $cols = null, callable $customSearchRowWithOffsetLimit = null): mixed
    {

        if ($customSearchRowWithOffsetLimit !== null){
            return $customSearchRowWithOffsetLimit($table, $searchTerm, $offset, $limit, $colToSearch, $cols);
        }

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
     * To handle the pagination yourselves, you need to pass an array of callables to $custom:
     *
     * - `customSearchTableCount`: you'll be passed: `$table, $searchTerm, $colToSearch` and it expects an int
     * - `customTableCount`: you'll be passed the `$table`, and expects an int
     * - `customSearchRowWithOffsetLimit`: you'll be passed `$table, $searchTerm, $offset, $limit, $colToSearch, $cols`, depending on how
     * you are handling the data, the expected type could be an object or an array.
     *  - `customGetRowWithOffsetLimit`: you'll be passed `$table, $offset, $limit,  $cols`, depending on how
     * you are handling the data, the expected type could be an object or an array.
     * @param string $cols
     * @param string $colToSearch
     * @param string $table
     * @param int $defaultPerPage
     * @param array|null $custom
     * @return object|null
     * @throws \Exception
     */
    public function generatePaginationData(
        string $cols,
        string $colToSearch,
        string $table,
        int $defaultPerPage = 20,
    array $custom = null): ?object
    {
        $customSearchTableCount = (isset($custom['customSearchTableCount'])) ? $custom['customSearchTableCount'] : null;
        $customTableCount = (isset($custom['customTableCount'])) ? $custom['customTableCount'] : null;
        $customSearchRowWithOffsetLimit = (isset($custom['customSearchRowWithOffsetLimit'])) ? $custom['customSearchRowWithOffsetLimit'] : null;
        $customGetRowWithOffsetLimit = (isset($custom['customGetRowWithOffsetLimit'])) ? $custom['customGetRowWithOffsetLimit'] : null;
        // remove token query string:
        url()->removeParam("token");
        $searchQuery = url()->getParam('query', '');
        if ($searchQuery){
            $tableRows = $this->getSearchTableCount(
                $searchQuery,
                $table,
                $colToSearch,
                $customSearchTableCount);
        } else {
            $tableRows = $this->getTableCount($table, $customTableCount);
        }

        return db()->paginate(
            tableRows: $tableRows,
            callback: function ($perPage, $offset) use ($customGetRowWithOffsetLimit, $customSearchRowWithOffsetLimit, $colToSearch, $table, $cols, $searchQuery){
                if ($searchQuery){
                    return $this->searchRowWithOffsetLimit(
                        $searchQuery, $offset, $perPage,
                        $table, $colToSearch, $cols, $customSearchRowWithOffsetLimit);
                } else {
                    return $this->getRowWithOffsetLimit($table, $offset, $perPage, $cols, $customGetRowWithOffsetLimit);
                }
            }, perPage: url()->getParam('per_page', $defaultPerPage));
    }

    /**
     * @param string $table
     * @param array $columns
     * @param string $colParam
     * Col to use for parameters, e.g menu_id
     * @param callable $onSuccess
     * @param callable $onError
     * @return void
     * @throws \Exception
     */
    public function deleteMultiple(string $table, array $columns, string $colParam, callable $onSuccess, callable $onError)
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
            db()->run("DELETE FROM $table WHERE `$colParam` IN ($questionMarks)", ...$parameter);
            $onSuccess();
        }catch (\Exception $e){
            $onError($e);
        }
    }

    /**
     * @throws \Exception
     */
    public function getPageStatus(): ?int
    {
        $status = 1;
        if (url()->getParam('page_action') === 'viewTrash') {
            $status = -1;
        }
        if (url()->getParam('page_action') === 'viewDraft') {
            $status = 0;
        }
        if (url()->getParam('page_action') === 'viewAll') {
            $status = null;
        }

        return $status;
    }
}