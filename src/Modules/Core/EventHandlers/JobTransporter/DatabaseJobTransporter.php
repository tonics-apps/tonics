<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers\JobTransporter;

use App\Apps\RefreshTrackUpdates\Jobs\TrackRefreshUpdateData;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\OnAddJobTransporter;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\JobSystem\JobTransporterInterface;
use App\Modules\Core\Library\MyPDO;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;

class DatabaseJobTransporter implements JobTransporterInterface, HandlerInterface
{
    use ConsoleColor;

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'Database';
    }

    public function getTable(): string
    {
        return Tables::getTable(Tables::JOBS);
    }

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddJobTransporter */
        $event->addJobTransporter($this);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function enqueue(AbstractJobInterface $jobEvent, callable $beforeEnqueue = null, callable $afterEnqueue = null): void
    {
        $toInsert = $this->getToInsert($jobEvent);
        if ($beforeEnqueue){
            $beforeEnqueue($toInsert);
        }

        if ($afterEnqueue){
            $returning = null;
            db(onGetDB: function (TonicsQuery $db) use ($afterEnqueue, $toInsert, &$returning){
                $returning = $db->InsertReturning($this->getTable(), $toInsert, Tables::$TABLES[Tables::JOBS], 'job_id');
            });
            $afterEnqueue($returning);
        } else {
            db(onGetDB: function ($db) use ($toInsert) {
                $db->insert($this->getTable(), $toInsert);
            });
        }

    }

    /**
     * @param AbstractJobInterface $jobEvent
     * @return array
     */
    public function getToInsert(AbstractJobInterface $jobEvent): array
    {
        return [
            'job_name' => $jobEvent->getJobName(),
            'job_parent_id' => $jobEvent->getJobParentID(),
            'job_status' => $jobEvent->getJobStatus(),
            'job_priority' => $jobEvent->getPriority(),
            'job_data' => json_encode([
                    'data' => $jobEvent->getData(),
                    'class' => get_class($jobEvent)]
            )
        ];
    }


    /**
     * @inheritDoc
     */
    public function isStatic(): bool
    {
        return false;
    }

    /**
     * @throws \Exception
     */
    public function runJob(): void
    {
        $table = $this->getTable();
        while (true) {
            if (AppConfig::isMaintenanceMode()){
                $this->infoMessage("Site in Maintenance Mode...Sleeping");
                usleep(5000000); # Sleep for 5 seconds
                continue;
            }

            /**
             * This query first selects all rows from the jobs table where the job_status is 'queued'.
             * It then uses a subquery to select all job_parent_id values from the jobs table that are not NULL.
             *
             * This effectively gets a list of all job_id values for jobs that have child jobs.
             * The outer query then filters out any jobs whose job_id is in the list of job_parent_id values, effectively excluding any jobs that have child jobs.
             *
             */
            $jobs = null;
            db(onGetDB: function ($db) use ($table, &$jobs){
                $jobs = $db->run(<<<SQL
SELECT * 
FROM $table
WHERE `job_status` = ? AND `job_id` NOT IN (SELECT `job_parent_id` FROM $table WHERE `job_parent_id` IS NOT NULL)
ORDER BY `job_priority` DESC
LIMIT ?
FOR UPDATE SKIP LOCKED
SQL, Job::JobStatus_Queued, 1);
            });


            if (empty($jobs)){
                # While the job is empty, we sleep for a 0.1s, this reduces the CPU usage, thus giving the CPU the chance to do other things
                usleep(100000);
                continue;
            }

            db(onGetDB: function (TonicsQuery $db) use ($jobs, $table) {
                foreach ($jobs as $job) {
                    try {
                        $this->infoMessage("Running job $job->job_name with an id of $job->job_id");
                        # Job In_Progress
                        $update = ['job_status' => Job::JobStatus_InProgress];
                        $db->FastUpdate($this->getTable(), $update, $db->Q()->WhereEquals('job_id', $job->job_id));

                        $this->handleIndividualJob($job);

                        $update = ['job_status' => Job::JobStatus_Processed, 'time_completed' => helper()->date()];
                        $db->FastUpdate($this->getTable(), $update, $db->Q()->WhereEquals('job_id', $job->job_id));
                        $this->infoMessage("Completed job $job->job_name with an id of $job->job_id");
                    } catch (\Throwable $exception) {
                        $update = ['job_status' => Job::JobStatus_Failed];
                        $this->errorMessage("Job $job->job_name failed, with an id of $job->job_id");
                        $db->FastUpdate($table, $update, $db->Q()->WhereEquals('job_id', $job->job_id));
                        $this->errorMessage($exception->getMessage() . $exception->getTraceAsString());
                    }
                }
            });

        }
    }

    /**
     * @param $job
     * @return void
     * @throws \Exception
     */
    public function handleIndividualJob($job): void
    {
        $jobData = json_decode($job->job_data);
        if (isset($jobData->class) && is_a($jobData->class, AbstractJobInterface::class, true)) {
            /** @var AbstractJobInterface $jobObject */
            $jobObject = container()->get($jobData->class);
            $jobObject->setData($jobData->data ?? []);
            if ($jobObject instanceof JobHandlerInterface) {
                $jobObject->handle();
            }
        }
    }
}