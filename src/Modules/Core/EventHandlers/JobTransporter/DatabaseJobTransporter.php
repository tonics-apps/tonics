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

class DatabaseJobTransporter implements JobTransporterInterface, HandlerInterface
{
    use ConsoleColor;

    private static MyPDO|null $db = null;

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
    public function enqueue(AbstractJobInterface $jobEvent): void
    {
        db()->insert($this->getTable(), $this->getToInsert($jobEvent));
    }

    /**
     * @param AbstractJobInterface $jobEvent
     * @return array
     */
    public function getToInsert(AbstractJobInterface $jobEvent): array
    {
        return [
            'job_name' => $jobEvent->getJobName(),
            'job_status' => Job::JobStatus_Queued,
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
        $db = db(true);
        $limit = AppConfig::getJobLimit();
        $table = $this->getTable();
        while (true) {
            if (AppConfig::isMaintenanceMode()){
                $this->infoMessage("Site in Maintenance Mode...Sleeping");
                usleep(5000000); # Sleep for 5 seconds
                continue;
            }
            $db->beginTransaction();

            /**
             * This query first selects all rows from the jobs table where the job_status is 'queued'.
             * It then uses a subquery to select all job_parent_id values from the jobs table that are not NULL.
             *
             * This effectively gets a list of all job_id values for jobs that have child jobs.
             * The outer query then filters out any jobs whose job_id is in the list of job_parent_id values, effectively excluding any jobs that have child jobs.
             *
             */
            $jobs = $db->run(<<<SQL
SELECT * 
FROM $table
WHERE `job_status` = ? AND `job_id` NOT IN (SELECT `job_parent_id` FROM $table WHERE `job_parent_id` IS NOT NULL)
ORDER BY `job_priority` DESC
LIMIT ?
FOR UPDATE
SQL, Job::JobStatus_Queued, $limit);

            if (empty($jobs)){
                $db->commit();
                # While the job is empty, we sleep for a 0.1s, this reduces the CPU usage, thus giving the CPU the chance to do other things
                usleep(100000);
                continue;
            }

            foreach ($jobs as $job) {
                try {
                    $this->infoMessage("Running job $job->job_name with an id of $job->job_id");
                    # Job In_Progress
                    $update = ['job_status' => Job::JobStatus_InProgress];
                    $db->FastUpdate($this->getTable(), $update, $db->Q()->WhereEquals('job_id', $job->job_id));

                    $this->handleIndividualJob($job);

                    $update = ['job_status' => Job::JobStatus_Processed, 'time_completed' => helper()->date()];
                    $db->FastUpdate($this->getTable(), $update, $db->Q()->WhereEquals('job_id', $job->job_id));
                } catch (\Throwable $exception) {
                    $update = ['job_status' => Job::JobStatus_Failed];
                    $this->infoMessage("Job $job->job_name failed, with an id of $job->job_id");
                    $db->FastUpdate($table, $update, $db->Q()->WhereEquals('job_id', $job->job_id));
                    $this->errorMessage($exception->getMessage());
                }
            }
            $db->commit();
        }
    }

    /**
     * @param $job
     * @return void
     */
    public function handleIndividualJob($job): void
    {
        $jobData = json_decode($job->job_data);
        if (isset($jobData->class) && is_a($jobData->class, AbstractJobInterface::class, true)) {
            /** @var AbstractJobInterface $jobObject */
            $jobObject = new $jobData->class;
            $jobObject->setData($jobData->data ?? []);
            if ($jobObject instanceof JobHandlerInterface) {
                $jobObject->handle();
            }
        }
    }
}