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

namespace App\Modules\Core\Library;

use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Validation\Traits\Validator;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Exception;

class AbstractDataLayer
{

    use Validator;

    const DataTableEventTypeSave           = 'SaveEvent';
    const DataTableEventTypeDelete         = 'DeleteEvent';
    const DataTableEventTypeUpdate         = 'UpdateEvent';
    const DataTableEventTypeAppUpdate      = 'AppUpdateEvent';
    const DataTableEventTypeUpsert         = 'UpsertEvent';
    const DataTableEventTypeCopyFieldItems = 'CopyFieldItemsEvent';

    const DataTableRetrieveHeaders           = 'headers';
    const DataTableRetrievePageSize          = 'pageSize';
    const DataTableRetrieveDeleteElements    = 'deleteElements';
    const DataTableRetrieveCopyFieldItems    = 'copyFieldItemsElements';
    const DataTableRetrieveUpdateElements    = 'updateElements';
    const DataTableRetrieveAppUpdateElements = 'appUpdateElements';
    const DataTableRetrieveFilterOption      = 'filterOption';


    /**
     * DON'T USE, USE THE STANDALONE db() funcion instead
     *
     * @param string $table
     *
     * @return mixed
     * @throws Exception
     */
    public function getTableCount (string $table): mixed
    {
        $count = null;
        db(onGetDB: function ($db) use ($table, &$count) {
            $count = $db->run("SELECT COUNT(*) AS 'r' FROM $table")[0]->r;
        });
        return $count;
    }

    /**
     * DON'T USE, USE THE STANDALONE db() funcion instead
     *
     * @param string $searchTerm
     * @param string $table
     * @param string $colToSearch
     *
     * @return mixed
     * @throws Exception
     */
    public function getSearchTableCount (string $searchTerm, string $table, string $colToSearch): mixed
    {
        $count = null;
        db(onGetDB: function ($db) use ($colToSearch, $table, $searchTerm, &$count) {
            $count = $db->run(<<<SQL
SELECT COUNT(*) AS 'r' FROM $table WHERE $colToSearch LIKE CONCAT('%', ?, '%')
SQL, $searchTerm)[0]->r;
        });

        return $count;
    }

    /**
     * DON'T USE, USE THE STANDALONE db() funcion instead
     *
     * @param string $table
     * @param $offset
     * @param $limit
     * @param string|null $cols
     *
     * @return mixed
     * @throws Exception
     */
    public function getRowWithOffsetLimit (string $table, $offset, $limit, string $cols = null): mixed
    {
        $offsetLimit = null;
        db(onGetDB: function ($db) use ($offset, $limit, $table, $cols, &$offsetLimit) {
            if ($cols !== null) {
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
     * DON'T USE, USE THE STANDALONE db() funcion instead
     *
     * @param string $searchTerm
     * @param $offset
     * @param $limit
     * @param string $table
     * @param string $colToSearch
     * @param string|null $cols
     *
     * @return mixed
     * @throws Exception
     */
    public function searchRowWithOffsetLimit (string $searchTerm, $offset, $limit, string $table, string $colToSearch, string $cols = null): mixed
    {
        $offsetLimit = null;
        db(onGetDB: function ($db) use ($colToSearch, $searchTerm, $offset, $limit, $table, $cols, &$offsetLimit) {
            if ($cols !== null) {
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
     * DON'T USE, USE THE STANDALONE db() funcion instead
     * Usage:
     * <br>
     * `$data->selectWithCondition('tablename', ['post_id', 'post_content'], "slug_id = ?", ['5475353']));`
     *
     * Note: Make sure you use a question-mark(?) in place u want a user input and pass the actual input in the $parameter
     *
     * @param string $table
     * @param array $colToSelect
     * To select all, use ['*']
     * @param string $whereCondition
     * @param array $parameter
     * @param bool $singleRow
     *
     * @return mixed
     * @throws \Exception
     */
    public function selectWithCondition (string $table, array $colToSelect, string $whereCondition, array $parameter, bool $singleRow = true): mixed
    {
        $select = helper()->returnDelimitedColumnsInBackTick($colToSelect);
        $data = null;

        db(onGetDB: function (TonicsQuery $db) use ($select, $parameter, $table, $whereCondition, $singleRow, $colToSelect, &$data) {

            if ($colToSelect === ['*']) {
                if ($singleRow) {
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

            if ($singleRow) {
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
     * The settings can have:
     *
     *  - query_name: Name of query in URL PARAM, use for search term
     *  - page_name: Name of page in URL PARAM, use for page size
     *  - per_page_name: Name to use for per_page in URL PARAM, use for the number of result to return in one go
     *
     * @param string $cols
     * @param string $colToSearch
     * @param string $table
     * @param int $defaultPerPage
     * @param array $settings
     *
     * @return object|null
     * @throws Exception
     */
    public function generatePaginationData (
        string $cols,
        string $colToSearch,
        string $table,
        int    $defaultPerPage = 20,
        array  $settings = []): ?object
    {


        $data = null;
        db(onGetDB: function (TonicsQuery $db) use ($defaultPerPage, $cols, $settings, $table, $colToSearch, &$data) {

            $queryName = (isset($settings['query_name'])) ? $settings['query_name'] : 'query';
            $pageName = (isset($settings['page_name'])) ? $settings['page_name'] : 'page';
            $perPage = (isset($settings['per_page_name'])) ? $settings['per_page_name'] : 'per_page';
            // remove token query string:
            url()->removeParam("token");
            $searchQuery = url()->getParam($queryName, '');
            if ($searchQuery) {
                $tableRows = $this->getSearchTableCount(
                    $searchQuery,
                    $table,
                    $colToSearch);
            } else {
                $tableRows = $this->getTableCount($table);
            }

            $data = $db->paginate(
                tableRows: $tableRows,
                callback: function ($perPage, $offset) use ($colToSearch, $table, $cols, $searchQuery) {
                    if ($searchQuery) {
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
     *
     * @return void
     * @throws Exception
     */
    public function deleteMultiple (
        string   $table,
        array    $columns,
        string   $colParam,
        array    $itemsToDelete = [],
        callable $onSuccess = null,
        callable $onError = null,
        string   $moreWhereCondition = ''): void
    {
        $parameter = [];
        $givenItemsToDelete = input()->fromPost()->retrieve('itemsToDelete', $itemsToDelete) ?: [];
        $itemsToDelete = array_map(function ($item) use ($colParam, $columns, &$parameter) {
            $itemCopy = [];
            if (helper()->isJSON($item)) {
                $itemCopy = json_decode($item, true);
            }

            if (is_array($item)) {
                $itemCopy = $item;
            }

            $item = [];
            foreach ($itemCopy as $k => $v) {
                if (key_exists($k, $columns)) {
                    if ($k === $colParam) {
                        $parameter[] = $v;
                    }
                    $item[$k] = $v;
                }
            }

            return $item;
        }, $givenItemsToDelete);


        try {
            if (!empty($itemsToDelete)) {
                $questionMarks = helper()->returnRequiredQuestionMarks([$itemsToDelete]);
                db(onGetDB: function (TonicsQuery $db) use ($parameter, $moreWhereCondition, $questionMarks, $colParam, $table) {
                    $db->run("DELETE FROM $table WHERE $colParam IN ($questionMarks) $moreWhereCondition", ...$parameter);
                });
                if ($onSuccess) {
                    $onSuccess();
                }
            }
        } catch (\Exception $e) {
            if ($onError) {
                $onError($e);
            }
        }
    }

    /**
     * @param string $type
     * @param $entityBag
     * @param $getEntityDecodedBagCallable
     *
     * @return bool
     * @throws \Throwable
     */
    public function isDataTableType (string $type, $entityBag = null, $getEntityDecodedBagCallable = null): bool
    {
        try {
            if ($entityBag === null) {
                $entityBag = json_decode(request()->getEntityBody());
            }

            if (isset($entityBag->type) && is_array($entityBag->type)) {
                if (in_array($type, $entityBag->type, true)) {
                    if ($getEntityDecodedBagCallable) {
                        $getEntityDecodedBagCallable($entityBag);
                    }
                    return true;
                }
            }
            return false;
        } catch (Exception $exception) {
            // log..
        }

        return false;
    }


    /**
     * @param string $toRetrieve
     * @param null $entityBag
     * @param null $getEntityDecodedBagCallable
     *
     * @return array
     * @throws \Throwable
     */
    public function retrieveDataFromDataTable (string $toRetrieve, $entityBag = null, $getEntityDecodedBagCallable = null): array
    {
        try {
            if ($entityBag === null) {
                $entityBag = json_decode(request()->getEntityBody());
            }
            if ($getEntityDecodedBagCallable) {
                $getEntityDecodedBagCallable($entityBag);
            }
            if (isset($entityBag->{$toRetrieve})) {
                return (array)$entityBag->{$toRetrieve};
            }
            return [];
        } catch (Exception $exception) {
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
     *
     * @param $tableCol
     * @param array $colsToValidate
     *
     * @return array
     * @throws Exception
     */
    public function validateTableColumnForDataTable ($tableCol, array $colsToValidate = []): array
    {
        $tblCol = explode('::', $tableCol) ?? [];
        # Table and column is invalid, should be in the format table::col
        if (count($tblCol) !== 2) {
            throw new \Exception("DataTable::Invalid table and column, should be in the format table::col");
        }

        if (!empty($colsToValidate)) {
            $colsToValidate = array_combine($colsToValidate, $colsToValidate);
            if (isset($colsToValidate[$tblCol[1]])) {
                # Col doesn't exist, we throw an exception
                if (!table()->hasColumn(DatabaseConfig::getPrefix() . $tblCol[0], $tblCol[1])) {
                    throw new \Exception("DataTable::Invalid col name $tblCol[1]");
                }
            }
        } else {
            # Col doesn't exist, we throw an exception
            if (!table()->hasColumn(DatabaseConfig::getPrefix() . $tblCol[0], $tblCol[1])) {
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
     *
     * @return bool
     * @throws Exception|\Throwable
     */
    public function dataTableDeleteMultiple (array $settings): bool
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

            if (is_callable($onBeforeDelete)) {
                $onBeforeDelete($toDelete);
            }

            db(onGetDB: function (TonicsQuery $db) use ($toDelete, $id, $table) {
                $db->FastDelete($table, db()->WhereIn($id, $toDelete));
            });

            apcu_clear_cache();
            if (is_callable($onSuccess)) {
                $onSuccess($toDelete);
            }
            $dbTx->commit();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            return true;
        } catch (\Exception $exception) {
            $dbTx->rollBack();
            $dbTx->getTonicsQueryBuilder()->destroyPdoConnection();
            // log..
            if (is_callable($onError)) {
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
     *
     * @return bool
     * @throws Exception|\Throwable
     */
    public function dataTableUpdateMultiple (array $settings): bool
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
                if (table()->hasColumn($table, 'field_settings')) {
                    $fieldSettings = $db->Select('field_settings')->From($table)->WhereEquals($id, $ID)->FetchFirst();
                    if (isset($fieldSettings->field_settings)) {
                        $fieldSettings = json_decode($fieldSettings->field_settings, true);
                        $fieldSettings = [...$fieldSettings, ...$updateChangesNoColPrefix];
                        $updateChanges[table()->getColumn($table, 'field_settings')] = json_encode($fieldSettings);
                    }
                }

                if (is_callable($onBeforeUpdate)) {
                    $onBeforeUpdate($updateChanges);
                }

                $db->FastUpdate($table, $updateChanges, db()->Where($id, '=', $ID));
                $db->getTonicsQueryBuilder()->destroyPdoConnection();
                if ($onSuccess) {
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
            if ($onError) {
                $onError();
            }
            return false;
            // log..
        }
    }

    /**
     * Example usage:
     *
     * ```
     * $db = db();
     * $db->Select('*')->From(Table::getTable(Table::TABLE_NAME))
     * cursorPaginate($result, ['slug_id']);
     *
     * # you would get the following data:
     * [
     *     data => [...],
     *     next_page_url => ...,    // gives you the full url with the next page cursor appended, useful in APIs
     *     prev_page_url => ...,    // gives you the full url with the prev page cursor appended, useful in APIs
     *     next_page_cursor => ..., // gives you the next_page_cursor string
     *     prev_page_cursor => ..., // gives you the prev_page_cursor string
     * ]
     *
     * to paginate to the next page or previous page, just pass the next/prev_page_cursor string here:
     *
     * cursorPaginate($result, ['slug_id'], 'xxx');
     * ```
     *
     * @param TonicsQuery $db
     *                                   The DB query in TonicsQuery
     * @param string|array $orderByColumn
     *                                   Column to orderBy, ensure this has a unique key in the table
     * @param null $cursor
     *                                   The cursor string
     * @param string|null $endPoint
     *                                   The request url endpoint, defaults to the current one if nones is supplied
     *
     * @return object
     * @throws \Throwable
     */
    public function cursorPaginate (TonicsQuery $db, string|array $orderByColumn, $cursor = null, string $endPoint = null): object
    {
        # We can use this to support ordering by multiple columns but not supported yet
        if (is_array($orderByColumn) && !empty($orderByColumn)) {
            $orderByColumn = $orderByColumn[array_key_first($orderByColumn)];
        }

        $limit = 30;
        $encodeCurs = fn($cursor, $nextPage) => urlencode(base64_encode(gzcompress(serialize([$cursor, $nextPage]), 9)));
        $decodeCurs = fn($cursor) => @unserialize(gzuncompress(base64_decode($cursor)));

        $newQ = $db->Q()
            ->Select('*')
            ->From(" ( {$db->getSqlString()} ) ")
            ->As('subquery')
            ->addParams($db->getParams());

        # Determine the direction of pagination
        [$decodedCursor, $nextPage] = $decodeCurs($cursor);
        $nextPage = $nextPage ?? true;

        # If user passes cursor we do not understand, we start from the beginning
        if (empty($decodedCursor)) {
            $cursor = null;
        }

        if ($cursor) {
            if (!$nextPage) {
                $newQ->Where($orderByColumn, '<', $decodedCursor)->OrderByDesc($orderByColumn);
            } else {
                $newQ->Where($orderByColumn, '>', $decodedCursor)->OrderByAsc($orderByColumn);
            }
        } else {
            $newQ->OrderByAsc($orderByColumn);
        }

        $newQ->Take($limit + 1);

        # Execute the query
        $results = $newQ->FetchResult();

        # If fetching previous page, reverse results for correct order
        if (!$nextPage) {
            $results = array_reverse($results);
        }

        # Check if there's a next or previous page
        $hasMore = count($results) > $limit;
        if ($hasMore) {
            # Remove the extra record from the results
            array_pop($results);
        }

        # Generate the next and previous cursors
        $nextCursor = null;
        $previousCursor = null;

        if (!empty($results)) {
            if ($nextPage) {
                $lastRecord = $results[array_key_last($results)];
                $nextCursor = $encodeCurs($lastRecord->{$orderByColumn}, true);
                $firstRecord = $results[array_key_first($results)];
                $previousCursor = $encodeCurs($firstRecord->{$orderByColumn}, false);
            } else {
                $firstRecord = $results[array_key_first($results)];
                $previousCursor = $encodeCurs($firstRecord->{$orderByColumn}, false);
                $lastRecord = $results[array_key_last($results)];
                $nextCursor = $encodeCurs($lastRecord->{$orderByColumn}, true);
            }
        }

        $generateUrl = function ($nextCursor = null, $previousCursor = null) use ($endPoint, &$mainCursorString) {
            $cloneRequest = request()->clone();
            $cloneRequest->setUrl($endPoint ?? $cloneRequest->getRequestURL());
            if ($nextCursor) {
                $cloneRequest->appendQueryString("cursor=$nextCursor");
                $mainCursorString = $nextCursor;
            } elseif ($previousCursor) {
                $cloneRequest->appendQueryString("cursor=$previousCursor");
                $mainCursorString = $previousCursor;
            }
            return $cloneRequest->getRequestURLWithQueryString();
        };

        return (object)[
            'data'             => $results,
            'first_page_url'   => $generateUrl(null, null),
            'next_page_url'    => $hasMore ? $generateUrl($nextCursor, null) : null,
            'prev_page_url'    => $cursor ? ($previousCursor ? $generateUrl(null, $previousCursor) : null) : null,
            'next_page_cursor' => $hasMore ? $nextCursor : null,
            'prev_page_cursor' => $cursor ? ($previousCursor ?: null) : null,
        ];

    }

}