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
use App\Modules\Core\Library\Database;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\JobSystem\JobTransporterInterface;
use App\Modules\Core\Library\MyPDO;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use ParagonIE\EasyDB\EasyDB;

class DatabaseJobTransporter implements JobTransporterInterface, HandlerInterface
{

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
        $this->getDB()->insert($this->getTable(), $this->getToInsert($jobEvent));
    }

    /**
     * @param AbstractJobInterface $jobEvent
     * @return array
     */
    public function getToInsert(AbstractJobInterface $jobEvent): array
    {
        return [
            'job_group_name' => $jobEvent->getJobGroupName(),
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
        while (true){
            $db = $this->getDB();
            $db->beginTransaction();
            $table = Tables::getTable(Tables::JOBS);
            $jobs = $db->run("SELECT * FROM $table WHERE job_status = 'queue' ORDER BY job_priority DESC LIMIT ? FOR UPDATE", AppConfig::getJobLimit());
            foreach ($jobs as $job){
                try {
                    $this->handleIndividualJob($job);
                    $insert = ['job_status' => Job::JobStatus_Processed];
                    $db->update($this->getTable(), $insert, ['job_id' => $job->job_id]);
                }catch (\Throwable){
                    $insert = ['job_status' => Job::JobStatus_Failed];
                    $db->update($this->getTable(), $insert, ['job_id' => $job->job_id]);
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
        if (isset($jobData->class) && is_a($jobData->class, AbstractJobInterface::class)){
            /** @var AbstractJobInterface $jobObject */
            $jobObject = new $jobData->class;
            $jobObject->setData($jobData->data ?? []);
            if ($jobObject instanceof JobHandlerInterface){
                $jobObject->handle();
            }
        }
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