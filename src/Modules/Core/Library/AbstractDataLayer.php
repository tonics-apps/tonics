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
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;

class AbstractDataLayer
{

    use Validator;

    const DataTableEventTypeSave = 'SaveEvent';
    const DataTableEventTypeDelete = 'DeleteEvent';
    const DataTableEventTypeUpdate = 'UpdateEvent';
    const DataTableEventTypeAppUpdate = 'AppUpdateEvent';
    const DataTableEventTypeUpsert = 'UpsertEvent';
    const DataTableEventTypeCopyFieldItems = 'CopyFieldItemsEvent';

    const DataTableRetrieveHeaders = 'headers';
    const DataTableRetrievePageSize = 'pageSize';
    const DataTableRetrieveDeleteElements = 'deleteElements';
    const DataTableRetrieveCopyFieldItems = 'copyFieldItemsElements';
    const DataTableRetrieveUpdateElements = 'updateElements';
    const DataTableRetrieveAppUpdateElements = 'appUpdateElements';
    const DataTableRetrieveFilterOption = 'filterOption';


    /**
     * @param string $table
     * @return mixed
     * @throws Exception
     */
    public function getTableCount(string $table): mixed
    {
        $count = null;
        db(onGetDB: function ($db) use ($table, &$count){
            $count = $db->run("SELECT COUNT(*) AS 'r' FROM $table")[0]->r;
        });
        return $count;
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
        $count = null;
        db(onGetDB: function ($db) use ($colToSearch, $table, $searchTerm, &$count){
            $count = $db->run(<<<SQL
SELECT COUNT(*) AS 'r' FROM $table WHERE $colToSearch LIKE CONCAT('%', ?, '%')
SQL, $searchTerm)[0]->r;
        });

        return $count;
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
        $offsetLimit = null;
        db(onGetDB: function ($db) use ($offset, $limit, $table, $cols, &$offsetLimit){
            if ($cols !== null){
                $offsetLimit = $db
                    ->run("SELECT $cols FROM $table LIMIT ? OFFSET ?", $limit, $offset);
                return;
            }

            $offsetLimit = $db
                ->run("SELECT * FROM $table LIMIT ? OFFSET ?", $limit, $offset);
        });

        return $offsetLimit;
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
        $offsetLimit = null;
        db(onGetDB: function ($db) use ($colToSearch, $searchTerm, $offset, $limit, $table, $cols, &$offsetLimit){
            if ($cols !== null){
                $offsetLimit = $db->run(<<<SQL
SELECT $cols FROM $table WHERE $colToSearch LIKE CONCAT('%', ?, '%') LIMIT ? OFFSET ?
SQL, $searchTerm, $limit, $offset);
                return;
            }

            $offsetLimit = $db->run(<<<SQL
SELECT * FROM $table WHERE $colToSearch LIKE CONCAT('%', ?, '%') LIMIT ? OFFSET ?
SQL, $searchTerm, $limit, $offset);
        });

        return $offsetLimit;

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
        $data = null;

        db(onGetDB: function (TonicsQuery $db) use ($select, $parameter, $table, $whereCondition, $singleRow, $colToSelect, &$data){

            if ($colToSelect === ['*']){
                if ($singleRow){
                    $data = $db->row(<<<SQL
SELECT * FROM $table WHERE $whereCondition
SQL, ...$parameter);
                    return;
                }

                $data = $db->run(<<<SQL
SELECT * FROM $table WHERE $whereCondition
SQL, ...$parameter);
                return;
            }

            if ($singleRow){
                $data = $db->row(<<<SQL
SELECT $select FROM $table WHERE $whereCondition
SQL, ...$parameter);
                return;
            }

            $data = $db->run(<<<SQL
SELECT $select FROM $table WHERE $whereCondition
SQL, ...$parameter);
        });

        return $data;

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
        db(onGetDB: function (TonicsQuery $db) use ($parameter, $whereCondition, $table) {
            $db->run(<<<SQL
DELETE FROM $table WHERE $whereCondition
SQL, ...$parameter);
        });
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


        $data = null;
        db(onGetDB: function (TonicsQuery $db) use ($defaultPerPage, $cols, $settings, $table, $colToSearch, &$data){

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

            $data = $db->paginate(
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
        });

        return $data;
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
                db(onGetDB: function (TonicsQuery $db) use ($parameter, $moreWhereCondition, $questionMarks, $colParam, $table) {
                    $db->run("DELETE FROM $table WHERE $colParam IN ($questionMarks) $moreWhereCondition", ...$parameter);
                });
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
     * @param array $settings
     * You can have the following options:
     *
     *  - id <br/>
     *  - table <br/>
     *  - entityBag <br/>
     *  - onBeforeDelete (you get what to delet in the callback param) <br/>
     *  - onSuccess (you get the deleted in the callback param)  <br/>
     *  - onError  <br/>
     * @return bool
     * @throws Exception
     */
    public function dataTableDeleteMultiple(array $settings): bool
    {
        $id = $settings['id'] ?? null;
        $table = $settings['table'] ?? null;
        $entityBag = $settings['entityBag'] ?? null;
        $onBeforeDelete = $settings['onBeforeDelete'] ?? null;
        $onSuccess = $settings['onSuccess'] ?? null;
        $onError = $settings['onError'] ?? null;

        $toDelete = [];
        $dbTx = db();
        $dbTx->beginTransaction();
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

            if (is_callable($onBeforeDelete)){
                $onBeforeDelete($toDelete);
            }

            db(onGetDB: function (TonicsQuery $db) use ($toDelete, $id, $table) {
                $db->FastDelete($table, db()->WhereIn($id, $toDelete));
            });

            apcu_clear_cache();
            if (is_callable($onSuccess)){
                $onSuccess($toDelete);
            }
            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            return true;
        } catch (\Exception $exception) {
            $dbTx->rollBack();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            // log..
            if (is_callable($onError)){
                $onError();
            }
            return false;
        }
    }

    /**
     * @param array $settings
     * You can have the following options:
     *
     *  - id <br/>
     *  - table <br/>
     *  - entityBag <br/>
     *  - rules <br/>
     *  - onBeforeUpdate (you get what to update in the callback param, this is not in bulk like the dataTableDeleteMultiple function, it is one at a time) <br/>
     *  - onSuccess <br/>
     *  - onError  <br/>
     * @return bool
     * @throws Exception
     */
    public function dataTableUpdateMultiple(array $settings): bool
    {
        $id = $settings['id'] ?? null;
        $table = $settings['table'] ?? null;
        $entityBag = $settings['entityBag'] ?? null;
        $rules = $settings['rules'] ?? [];
        $onBeforeUpdate = $settings['onBeforeUpdate'] ?? null;
        $onSuccess = $settings['onSuccess'] ?? null;
        $onError = $settings['onError'] ?? null;

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

                if (is_callable($onBeforeUpdate)){
                    $onBeforeUpdate($updateChanges);
                }

                $db->FastUpdate($table, $updateChanges, db()->Where($id, '=', $ID));
                $db->getTonicsQueryBuilder()->destroyPdoConnection();
                if ($onSuccess){
                    $onSuccess($colForEvent, $entityBag);
                }
            }
            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            apcu_clear_cache();
            return true;
        } catch (\Exception $exception) {
            $dbTx->rollBack();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            if ($onError){
                $onError();
            }
            return false;
            // log..
        }
    }
}