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
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class JobManagerController
{
    public function jobsIndex()
    {

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
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_parent_name', 'title' => 'Depends On', 'minmax' => '150px, 1.6fr', 'td' => 'schedule_parent_name'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_ticks', 'title' => 'Ticks', 'minmax' => '50px, .5fr', 'td' => 'schedule_ticks'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_priority', 'title' => 'Priority', 'minmax' => '50px, .5fr', 'td' => 'schedule_priority'],
            ['type' => '', 'slug' => Tables::SCHEDULER . '::' . 'schedule_every', 'title' => 'Schedule Every', 'minmax' => '50px, .5fr', 'td' => 'schedule_every'],
            ['type' => 'date_time_local', 'slug' => Tables::SCHEDULER . '::' . 'schedule_next_run', 'title' => 'Next Run', 'minmax' => '150px, 1fr', 'td' => 'schedule_next_run'],
        ];

        $data = db()->Select('*')
            ->From($table)
            ->when(url()->hasParamAndValue('query'), function (TonicsQuery $db) {
                $db->WhereLike('schedule_name', url()->getParam('query'));

            })->when(url()->hasParamAndValue('start_date') && url()->hasParamAndValue('end_date'), function (TonicsQuery $db) use ($table) {
                $db->WhereBetween(table()->pickTable($table, ['created_at']), db()->DateFormat(url()->getParam('start_date')), db()->DateFormat(url()->getParam('end_date')));

            })->OrderByDesc(table()->pickTable($table, ['updated_at']))->SimplePaginate(url()->getParam('per_page', AppConfig::getAppPaginationMax()));

        view('Modules::Track/Views/License/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => $data ?? []
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);
    }
}