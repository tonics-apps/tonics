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
    public function __construct (AbstractDataLayer $dataLayer)
    {
        $this->dataLayer = $dataLayer;
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function jobsIndex (): void
    {
        $table = Tables::getTable(Tables::JOBS);
        $dataTableHeaders = [
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'job_id', 'title' => 'ID', 'minmax' => '50px, .5fr', 'td' => 'parent_id'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'job_name', 'title' => 'Name', 'minmax' => '150px, 1.6fr', 'td' => 'parent_name'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'job_status', 'title' => 'Status', 'minmax' => '50px, .5fr', 'td' => 'job_status'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'total_child_jobs', 'title' => 'Child Jobs', 'minmax' => '50px, .5fr', 'td' => 'total_child_jobs'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'completed_child_jobs', 'title' => 'Processed Child Jobs', 'minmax' => '80px, 1fr', 'td' => 'completed_child_jobs'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'failed_child_jobs', 'title' => 'Failed Child Jobs', 'minmax' => '80px, 1fr', 'td' => 'failed_child_jobs'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'created_at', 'title' => 'Created At', 'minmax' => '100px, 1fr', 'td' => 'created_at'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'time_completed', 'title' => 'Completed At', 'minmax' => '100px, 1fr', 'td' => 'time_completed'],
            ['type' => '', 'slug' => Tables::JOBS . '::' . 'overall_progress', 'title' => 'Progress', 'minmax' => '50px, .5fr', 'td' => 'overall_progress'],
        ];

        /**
         * The CASE expression in the SELECT clause is used to calculate the overall progress of each parent job.
         * If the parent job has no child jobs and its job_status is 'processed', the overall progress is 100.
         * If the parent job has child jobs or its job_status is not 'processed', the overall progress will be calculated using the percentage of completed child jobs.
         *
         * The WHERE clause filters the rows in the tonics_jobs table to only include the parent jobs that have no parent job.
         * The GROUP BY clause groups the rows by the job_name column, so that the COUNT and SUM aggregate functions are calculated for each parent job.
         */
        $select = "p.job_id as parent_id, p.job_name AS parent_name, p.job_status, p.created_at, p.time_completed, p.job_priority,
        COUNT(j.job_id) AS total_child_jobs,
        SUM(j.job_status = 'processed') AS completed_child_jobs, 
        SUM(j.job_status = 'failed') AS failed_child_jobs,
        CASE
            WHEN COUNT(j.job_id) = 0 AND p.job_status = 'processed' THEN 100
            ELSE ROUND((SUM(j.job_status = 'processed') / COUNT(*)) * 100)
        END AS overall_progress";

        $data = null;
        db(onGetDB: function ($db) use ($table, $select, &$data) {
            $data = $db->Select($select)
                ->From("$table p")
                ->LeftJoin("$table j", 'j.job_parent_id', 'p.job_id')
                ->WhereNull('p.job_parent_id')
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('p.job_name', url()->getParam('query'));
                })
                ->GroupBy('p.job_name')->GroupBy('p.job_id')
                ->OrderByDesc('p.job_priority')->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        });

        view('Modules::Core/Views/JobsManager/jobs_index', [
            'DataTable' => [
                'headers'      => $dataTableHeaders,
                'paginateData' => $data ?? [],
            ],
            'SiteURL'   => AppConfig::getAppUrl(),
        ]);

    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function jobDataTable (): void
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
     *
     * @return bool|int
     * @throws \Throwable
     */
    protected function jobDeleteMultiple ($entityBag): bool|int
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

            db(onGetDB: function ($db) use ($toDelete) {
                $db->FastDelete(Tables::getTable(Tables::JOBS), db()->WhereIn('job_id', $toDelete));
            });

            return true;
        } catch (\Exception $exception) {
            // log..
            return false;
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function jobsSchedulerIndex (): void
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

        $data = null;
        db(onGetDB: function ($db) use ($table, &$data) {
            $data = $db->Select('*')
                ->From($table)
                ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                    $db->WhereLike('schedule_name', url()->getParam('query'));
                })->OrderByAsc(table()->pickTable($table, ['schedule_next_run']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));
        });

        view('Modules::Core/Views/JobsManager/jobs_scheduler_index', [
            'DataTable' => [
                'headers'      => $dataTableHeaders,
                'paginateData' => $data ?? [],
            ],
            'SiteURL'   => AppConfig::getAppUrl(),
        ]);
    }

    /**
     * @return AbstractDataLayer
     */
    public function getDataLayer (): AbstractDataLayer
    {
        return $this->dataLayer;
    }
}