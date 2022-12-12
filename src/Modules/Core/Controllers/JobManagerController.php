<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class JobManagerController
{
    private AbstractDataLayer $dataLayer;

    /**
     * @param AbstractDataLayer $dataLayer
     */
    public function __construct(AbstractDataLayer $dataLayer)
    {
        $this->dataLayer = $dataLayer;
    }

    /**
     * @throws \Exception
     */
    public function jobsIndex()
    {
        $table = Tables::getTable(Tables::JOBS);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'job_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'parent_id'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'job_name', 'title' => 'Name', 'minmax' => '150px, 1.6fr', 'td' => 'parent_name'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'job_status', 'title' => 'Status', 'minmax' => '50px, .5fr', 'td' => 'job_status'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'total_child_jobs', 'title' => 'Child Jobs', 'minmax' => '50px, .5fr', 'td' => 'total_child_jobs'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'completed_child_jobs', 'title' => 'Processed Child Jobs', 'minmax' => '80px, 1fr', 'td' => 'completed_child_jobs'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'created_at', 'title' => 'Created At', 'minmax' => '100px, 1fr', 'td' => 'created_at'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'time_completed', 'title' => 'Completed At', 'minmax' => '100px, 1fr', 'td' => 'time_completed'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'overall_progress', 'title' => 'Progress', 'minmax' => '50px, .5fr', 'td' => 'overall_progress'],
        ];

        /**
         * The CASE expression in the SELECT clause is used to calculate the overall progress of each parent job.
         * If the parent job has no child jobs  and its job_status is 'processed', the overall progress is 100.
         * If the parent job has child jobs or its job_status is not 'processed', the overall progress will be calculated using the percentage of completed child jobs.
         *
         * The WHERE clause filters the rows in the tonics_jobs table to only include the parent jobs that have no parent job.
         * The GROUP BY clause groups the rows by the job_name column, so that the COUNT and SUM aggregate functions are calculated for each parent job.
         */
        $select = "p.job_id as parent_id, p.job_name AS parent_name, p.job_status, p.created_at, p.time_completed, p.job_priority,
        COUNT(j.job_id) AS total_child_jobs,
        SUM(j.job_status = 'processed') AS completed_child_jobs, 
        CASE
            WHEN COUNT(j.job_id) = 0 AND p.job_status = 'processed' THEN 100
            ELSE ROUND((SUM(j.job_status = 'processed') / COUNT(*)) * 100)
        END AS overall_progress";

        $db = db();
        $data = $db->Select($select)
            ->From("$table p")
            ->LeftJoin("$table j", 'j.job_parent_id', 'p.job_id')
            ->WhereNull('p.job_parent_id')
            ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('p.job_name', url()->getParam('query'));
            })
            ->GroupBy('p.job_name')
            ->OrderByDesc('p.job_priority')->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Core/Views/JobsManager/jobs_index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? []
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);

    }

    /**
     * @throws \Exception
     */
    public function jobDataTable(): void
    {
        $entityBag = null;
        if ($this->getDataLayer()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            if ($this->jobDeleteMultiple($entityBag)) {
                response()->onSuccess([], "Records Deleted", more: AbstractDataLayer::DataTableEventTypeDelete);
            } else {
                response()->onError(500);
            }
        }
    }

    /**
     * @param $entityBag
     * @return bool|int
     */
    protected function jobDeleteMultiple($entityBag): bool|int
    {
        $toDelete = [];
        try {
            $deleteItems = $this->getDataLayer()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
            foreach ($deleteItems as $deleteItem) {
                foreach ($deleteItem as $col => $value) {
                    $tblCol = $this->getDataLayer()->validateTableColumnForDataTable($col, ['job_id']);
                    if ($tblCol[1] === 'job_id') {
                        $toDelete[] = $value;
                    }
                }
            }

            db()->FastDelete(Tables::getTable(Tables::JOBS), db()->WhereIn('job_id', $toDelete));
            return true;
        } catch (\Exception $exception) {
            // log..
            return false;
        }
    }

    /**
     * @throws \Exception
     */
    public function jobsSchedulerIndex()
    {
        $table = Tables::getTable(Tables::SCHEDULER);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'schedule_id'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_name', 'title' => 'Title', 'minmax' => '150px, 1.6fr', 'td' => 'schedule_name'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_parent_name', 'title' => 'Depends On', 'minmax' => '100px, 1fr', 'td' => 'schedule_parent_name'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_ticks', 'title' => 'Ticks', 'minmax' => '50px, .5fr', 'td' => 'schedule_ticks'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_priority', 'title' => 'Priority', 'minmax' => '50px, .5fr', 'td' => 'schedule_priority'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_every', 'title' => 'Schedule Every', 'minmax' => '10px, 1fr', 'td' => 'schedule_every'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_next_run', 'title' => 'Next Run', 'minmax' => '150px, 1fr', 'td' => 'schedule_next_run'],
        ];

        $data = db()->Select('*')
            ->From($table)
            ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('schedule_name', url()->getParam('query'));
            })->OrderByAsc(table()->pickTable($table, ['schedule_next_run']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Core/Views/JobsManager/jobs_scheduler_index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? []
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @return AbstractDataLayer
     */
    public function getDataLayer(): AbstractDataLayer
    {
        return $this->dataLayer;
    }
}