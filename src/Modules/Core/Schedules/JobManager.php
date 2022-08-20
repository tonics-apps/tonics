<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Schedules;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\Database;
use App\Modules\Core\Library\MyPDO;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\Tables;
use ParagonIE\EasyDB\EasyDB;

/**
 * The JobManager is a long-running process, so, the schedule manager should at least check it every hour
 * if it isn't running, and then run it.
 */
class JobManager extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{
    use ConsoleColor;

    public function __construct()
    {
        $this->setName('Core_JobManager');
        $this->setPriority(Scheduler::PRIORITY_EXTREME);
        $this->setEvery(Scheduler::everyHour(1));
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        while (true){
            $jobs = $this->getQueueJobs();
            foreach ($jobs as $job){
                $jobData = json_decode($job->job_data);
                // $jobClass =
            }
        }
        $this->infoMessage($this->getName());
    }

    /**
     * @throws \Exception
     */
    public function getQueueJobs(): object|array|bool|int
    {
        $table = Tables::getTable(Tables::JOBS);
        return $this->getDB()->run("SELECT * FROM $table WHERE job_status = 'queue' ORDER BY job_priority DESC LIMIT ? FOR UPDATE", AppConfig::getJobLimit());
    }

    /**
     * @return MyPDO|EasyDB
     * @throws \Exception
     */
    public function getDB(): MyPDO|EasyDB
    {
        return (new Database())->createNewDatabaseInstance();
    }

}