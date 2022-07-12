<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Library;

use ParagonIE\EasyDB\EasyDB;
use PDO;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class MyPDO extends EasyDB
{
    /**
     * @param string $dbEngine
     */
    public function setDbEngine(string $dbEngine): void
    {
        $this->dbEngine = $dbEngine;
    }

    /**
     *
     * FOR BATCH INSERTING.... PLEASE USE THIS WHEN ONLY INSERTING IN BATCH
     * USE THE SIMPLE INSERT FOR ONE OFF INSERT INSTEAD
     * <br>
     * @param $table
     * name of the table
     * @param $data
     * should be in this format:
     * <code>
     * [
     *  ["genre_name" => "Acoustic", "genre_slug" => "acoustic", "genre_description" => "Acoustic"],
     *  ["genre_name" => "Afrobeat", "genre_slug" => "afrobeat", "genre_description" => "Afrobeat"]
     * ]
     *  array_key is the table_column, and array_value is the value of the column
     *
     * @throws \Exception
     */
    public function insertBatch(string $table, array $data): bool
    {

        if (empty($data)) return false;

        if (!is_array(reset($data))) $data = [$data];

        # This gets the array_keys of the first element in the array
        # which would act as the columns of all the array
        $getColumns = array_keys(reset($data));
        # e.g, "`column1`,`column1`,`column1`",
        $delimitedColumns = helper()->returnDelimitedColumnsInBackTick($getColumns);
        $numberOfQ = helper()->returnRequiredQuestionMarksSurroundedWithParenthesis($data);
        # we would throw away the keys in the multi-dimensional array and flatten it into 1d array
        $flattened = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($data)), 0);
        # SQL
        $sql = <<<SQL
INSERT INTO $table ($delimitedColumns) VALUES $numberOfQ;
SQL;
        # Prepare Statement and execute it ;P
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($flattened);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * @param int $tableRows
     * The total number of rows that can be returned in the query you'll be performing
     * @param \Closure $callback
     * You would get the perPage and offset in the callback parameter which you can then do some queries with and return us the result of the query....
     * So, we can take care of the rest of the pagination implementation
     * @param int $perPage
     * How many rows to retrieve per page when paginating
     * @param string $pageName
     * The page url query name, i.e ?page=20 (the pagename is `page`, which tells us what page to move to in the pagination window)
     * @return object|null
     * @throws \Exception
     */
    public function paginate(
        int $tableRows,
        \Closure $callback,
        int $perPage = 5,
        string $pageName = 'page',
    ): ?object
    {

        #
        # The reason for doing ($tableRows / $perPage) is to determine the number of total pages we can paginate through
        $totalPages = (int)ceil($tableRows / $perPage);

        # Reset request(), we might have something cached in params (this is very important)
        url()->reset();

        #
        # current page - The page the user is currently on, if we can't find the page number, we default to the first page
        $page = url()->getParam($pageName, null, function ($value) use ($pageName, $tableRows) {
            return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        }) ?: 1;

        #
        # Get the Offset based on the current page
        # The offset would determine the numbers of rows to skip before
        # returning result.
        $offset = ($page - 1) * $perPage;

        $result = $callback($perPage, $offset);
        if ($result) {
            #
            # ARRANGE THE PAGINATION RESULT
            return $this->arrangePagination(
                sqlResult: $result,
                page: $page,
                totalPages: $totalPages,
                perPage: $perPage, pageName: $pageName);
        }
        return null;
    }

    /**
     * @param $sqlResult
     * @param $page
     * @param $totalPages
     * @param $perPage
     * @param $pageName
     * @return object
     * @throws \Exception
     */
    private function arrangePagination($sqlResult, $page, $totalPages, $perPage, $pageName): object
    {
        $currentUrl = url()->getRequestURLWithQueryString();
        $numberLinks = []; $windowSize = 5;
        if ($page > 1) {;
            // Number links that should appear on the left
            for($i = $page - $windowSize; $i < $page; $i++){
                if($i > 0){
                    $numberLinks[] = [
                        'link' => url()->appendQueryString("$pageName=" . $i)->getRequestURLWithQueryString(),
                        'number' => $i,
                        'current' => false,
                        'current_text' => 'Page Number ' . $i,
                    ];
                }
            }
        }
        // current page
        $numberLinks[] = [
            'link' => url()->appendQueryString("$pageName=" . $page)->getRequestURLWithQueryString(),
            'number' => $page,
            'current' => true,
            'current_text' => 'Current Page',
        ];
        // Number links that should appear on the right
        for($i = $page + 1; $i <= $totalPages; $i++){
            $numberLinks[] = [
                'link' => url()->appendQueryString("$pageName=" . $i)->getRequestURLWithQueryString(),
                'number' => $i,
                'current' => false,
                'current_text' => 'Page Number ' . $i,
            ];
            if($i >= $page + $windowSize){
                break;
            }
        }

        return (object)[
            'current_page' => (int)$page,
            'data' => $sqlResult,
            'path' => $currentUrl,
            'first_page_url' => url()->appendQueryString("$pageName=1")->getRequestURLWithQueryString(),
            'next_page_url' => ($page != $totalPages) ? url()->appendQueryString("$pageName=" . ($page + 1))->getRequestURLWithQueryString() : null,
            'prev_page_url' => ($page > 1) ? url()->appendQueryString("$pageName=" . ($page - 1))->getRequestURLWithQueryString() : null,
            'from' => 1,
            'next_page' => ($page != $totalPages) ? $page + 1: null,
            'last_page' => $totalPages,
            'last_page_url' => url()->appendQueryString("$pageName=" . $totalPages)->getRequestURLWithQueryString(),
            'per_page' => $perPage,
            'to' => $totalPages,
            'total' => $totalPages,
            'has_more' => !(((int)$page === $totalPages)),
            'number_links' => $numberLinks
        ];

    }

    /**
     * This Either Insert or Update New Record if matched by unique or primary key
     * @param string $table
     * Table name
     * @param array $data
     * Data To Insert
     * @param array $update
     * Update Keys
     * @param int $chunkInsertRate
     * How many records to insert at a time, the default is okay, but you can experiment with more
     * @return false
     * @throws \Exception
     */
    public function insertOnDuplicate(string $table, array $data, array $update, int $chunkInsertRate = 1000): bool
    {

        if (empty($data)) return false;

        if (!is_array(reset($data))) $data = [$data];

        #
        # VALIDATION AND DELIMITATION FOR $data
        #
        $getColumns = array_keys(reset($data));
        $delimitedColumns = helper()->returnDelimitedColumnsInBackTick($getColumns);

        #
        # Chunking Operation Begins
        #
        foreach (array_chunk($data, $chunkInsertRate) as $toInsert){
            $numberOfQ = helper()->returnRequiredQuestionMarksSurroundedWithParenthesis($toInsert);

            if (!is_array(reset($update))){
                $update = [$update];
            }
            $update = array_values(reset($update));
            #
            # SQL PREPARE, INSERTION AND DATA RETURNING
            #
            $delimitedForInsertOnDuplicate = helper()->delimitedForInsertOnDuplicate($update);
            $flattened = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($toInsert)), 0);

            $sql = <<<SQL
INSERT INTO $table ($delimitedColumns) VALUES $numberOfQ ON DUPLICATE KEY UPDATE $delimitedForInsertOnDuplicate
SQL;
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($flattened);
        }
        return true;
    }

    /**
     * Insert and Return Data specified in $return
     * @param string $table
     * @param array $data
     * @param array $return
     * @return \stdClass|bool
     * @throws \Exception
     */
    public function insertReturning(string $table, array $data, array $return): \stdClass|bool
    {
        if (empty($data)) return false;

        if (!is_array(reset($data))) $data = [$data];

        if (!is_array(reset($return))) $return = [$return];

        #
        # VALIDATION AND DELIMITATION FOR RETURNING
        #
        $getReturningColumns = (array)array_values(reset($return));
        $delimitedReturningColumns = helper()->returnDelimitedColumnsInBackTick($getReturningColumns);

        #
        # VALIDATION AND DELIMITATION FOR THE ACTUAL COLUMN
        #
        $getColumns = array_keys(reset($data));
        # e.g, "`column1`,`column1`,`column1`",
        $delimitedColumns = helper()->returnDelimitedColumnsInBackTick($getColumns);
        $numberOfQ = helper()->returnRequiredQuestionMarksSurroundedWithParenthesis($data);

        #
        # SQL PREPARE, INSERTION AND DATA RETURNING
        #
        $sql = <<<SQL
INSERT INTO $table ($delimitedColumns) VALUES $numberOfQ RETURNING $delimitedReturningColumns
SQL;
        $stmt = $this->getPdo()->prepare($sql);
        $flattened = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($data)), 0);
        $stmt->execute($flattened);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}