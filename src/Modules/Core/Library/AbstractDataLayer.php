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

use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Validation\Traits\Validator;
use Exception;

class AbstractDataLayer
{

    use Validator;

    const DataTableEventTypeSave = 'SaveEvent';
    const DataTableEventTypeDelete = 'DeleteEvent';
    const DataTableEventTypeUpdate = 'UpdateEvent';
    const DataTableEventTypeAppUpdate = 'AppUpdateEvent';
    const DataTableEventTypeUpsert = 'UpsertEvent';

    const DataTableRetrieveHeaders = 'headers';
    const DataTableRetrievePageSize = 'pageSize';
    const DataTableRetrieveDeleteElements = 'deleteElements';
    const DataTableRetrieveUpdateElements = 'updateElements';
    const DataTableRetrieveAppUpdateElements = 'appUpdateElements';
    const DataTableRetrieveFilterOption = 'filterOption';

    /**
     * Database you wanna Interact With...
     * This would by default connect with the db in the config
     */
    protected MyPDO $DB;


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
     * Col to use for parameters, e.g. menu_id
     * @param array $itemsToDelete
     * @param callable|null $onSuccess
     * @param callable|null $onError
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
        array $itemsToDelete = [],
        callable $onSuccess = null,
        callable $onError = null,
        string $moreWhereCondition = ''): void
    {
        $parameter = [];
        $givenItemsToDelete = input()->fromPost()->retrieve('itemsToDelete', $itemsToDelete) ?: [];
        $itemsToDelete = array_map(function ($item) use ($colParam, $columns, &$parameter){
            $itemCopy = [];
            if (helper()->isJSON($item)){
                $itemCopy = json_decode($item, true);
            }

            if (is_array($item)){
                $itemCopy = $item;
            }

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
        }, $givenItemsToDelete);


        try {
            if (!empty($itemsToDelete)){
                $questionMarks = helper()->returnRequiredQuestionMarks([$itemsToDelete]);
                db()->run("DELETE FROM $table WHERE $colParam IN ($questionMarks) $moreWhereCondition", ...$parameter);
                if ($onSuccess){
                    $onSuccess();
                }
            }
        }catch (\Exception $e){
            if ($onError){
                $onError($e);
            }
        }
    }

    /**
     * @param string $type
     * @param $entityBag
     * @param $getEntityDecodedBagCallable
     * @return bool
     */
    public function isDataTableType(string $type, $entityBag = null, $getEntityDecodedBagCallable = null): bool
    {
        try {
            if ($entityBag === null){
                $entityBag = json_decode(request()->getEntityBody());
            }

            if (isset($entityBag->type) && is_array($entityBag->type)){
                if (in_array($type, $entityBag->type, true)) {
                    if ($getEntityDecodedBagCallable){
                        $getEntityDecodedBagCallable($entityBag);
                    }
                    return true;
                }
            }
            return false;
        } catch (Exception $exception){
            // log..
        }

        return false;
    }


    /**
     * @param string $toRetrieve
     * @param null $entityBag
     * @param null $getEntityDecodedBagCallable
     * @return array
     */
    public function retrieveDataFromDataTable(string $toRetrieve, $entityBag = null, $getEntityDecodedBagCallable = null): array
    {
        try {
            if ($entityBag === null){
                $entityBag = json_decode(request()->getEntityBody());
            }
            if ($getEntityDecodedBagCallable){
                $getEntityDecodedBagCallable($entityBag);
            }
            if (isset($entityBag->{$toRetrieve})){
                return (array)$entityBag->{$toRetrieve};
            }
            return [];
        } catch (Exception $exception){
            // log..
        }

        return [];
    }

    /**
     * This validates table and column from datatable, if the validation doesn't throw
     * an exception, you get the table name in the 0 index and the column name in the 1 index.
     *
     * <br>
     * If `$colsToValidate` is given, then we only valid the columns in the given array
     * @param $tableCol
     * @param array $colsToValidate
     * @return array
     * @throws Exception
     */
    public function validateTableColumnForDataTable($tableCol, array $colsToValidate = []): array
    {
        $tblCol = explode('::', $tableCol) ?? [];
        # Table and column is invalid, should be in the format table::col
        if (count($tblCol) !== 2){
            throw new \Exception("DataTable::Invalid table and column, should be in the format table::col");
        }

        if (!empty($colsToValidate)){
            $colsToValidate = array_combine($colsToValidate, $colsToValidate);
            if (isset($colsToValidate[$tblCol[1]])){
                # Col doesn't exist, we throw an exception
                if(!table()->hasColumn(DatabaseConfig::getPrefix().$tblCol[0], $tblCol[1])){
                    throw new \Exception("DataTable::Invalid col name $tblCol[1]");
                }
            }
        } else {
            # Col doesn't exist, we throw an exception
            if(!table()->hasColumn(DatabaseConfig::getPrefix().$tblCol[0], $tblCol[1])){
                throw new \Exception("DataTable::Invalid col name $tblCol[1]");
            }
        }

        return $tblCol;
    }

    /**
     * @param string $id
     * @param string $table
     * @param $entityBag
     * @param callable|null $onSuccess
     * @param callable|null $onError
     * @return bool
     */
    public function dataTableDeleteMultiple(string $id, string $table, $entityBag, callable $onSuccess = null, callable $onError = null): bool
    {
        $toDelete = [];
        try {
            $deleteItems = $this->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
            foreach ($deleteItems as $deleteItem) {
                foreach ($deleteItem as $col => $value) {
                    $tblCol = $this->validateTableColumnForDataTable($col);
                    if ($tblCol[1] === $id) {
                        $toDelete[] = $value;
                    }
                }
            }

            db()->FastDelete($table, db()->WhereIn($id, $toDelete));
            apcu_clear_cache();
            if ($onSuccess){
                $onSuccess();
            }
            return true;
        } catch (\Exception $exception) {
            // log..
            if ($onError){
                $onError();
            }
            return false;
        }
    }

    /**
     * @param string $id
     * @param string $table
     * @param $entityBag
     * @param array $rules
     * @param callable|null $onSuccess
     * @param callable|null $onError
     * @return bool
     * @throws Exception
     */
    public function dataTableUpdateMultiple(string $id, string $table, $entityBag, array $rules = [], callable $onSuccess = null, callable $onError = null): bool
    {
        $dbTx = db();
        try {
            $updateItems = $this->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveUpdateElements, $entityBag);
            $dbTx->beginTransaction();
            foreach ($updateItems as $updateItem) {
                $db = db();
                $updateChanges = [];
                $updateChangesNoColPrefix = [];
                $colForEvent = [];
                foreach ($updateItem as $col => $value) {
                    $tblCol = $this->validateTableColumnForDataTable($col);
                    $tableName = DatabaseConfig::getPrefix() . $tblCol[0];
                    # We get the column (this also validates the table)
                    $setCol = table()->getColumn(table()->getTable($tableName), $tblCol[1]);

                    $colForEvent[$tblCol[1]] = $value;
                    $updateChanges[$setCol] = $value;
                    $updateChangesNoColPrefix[$tblCol[1]] = $value;
                }

                # Validate The col and type
                $validator = $this->getValidator()->make($colForEvent, $rules);
                if ($validator->fails()) {
                    throw new \Exception("DataTable::Validation Error {$validator->errorsAsString()}");
                }

                $ID = $updateChanges[table()->getColumn($table, $id)];
                if (table()->hasColumn($table, 'field_settings')){
                    $fieldSettings = $db->Select('field_settings')->From($table)->WhereEquals($id, $ID)->FetchFirst();
                    if (isset($fieldSettings->field_settings)){
                        $fieldSettings = json_decode($fieldSettings->field_settings, true);
                        $fieldSettings = [...$fieldSettings, ...$updateChangesNoColPrefix];
                        $updateChanges[table()->getColumn($table, 'field_settings')] = json_encode($fieldSettings);
                    }
                }
                $db->FastUpdate($table, $updateChanges, db()->Where($id, '=', $ID));
                if ($onSuccess){
                    $onSuccess($colForEvent, $entityBag);
                }
            }
            $dbTx->commit();
            apcu_clear_cache();
            return true;
        } catch (\Exception $exception) {
            $dbTx->rollBack();
            if ($onError){
                $onError();
            }
            return false;
            // log..
        }
    }
}