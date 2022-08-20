<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\JobSystem;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;

class AbstractJobInterface
{
    private string $jobGroupName = '';
    private string $jobStatus = self::JobStatus_Queued;
    private int $priority = Scheduler::PRIORITY_MEDIUM;
    private mixed $data = null;

    const JobStatus_Queued = 'queued';
    const JobStatus_Processing = 'processing';
    const JobStatus_Processed = 'processed';
    const JobStatus_Failed = 'processed';

    /**
     * @return string
     */
    public function getJobGroupName(): string
    {
        return $this->jobGroupName;
    }

    /**
     * @param string $jobGroupName
     * @return AbstractJobInterface
     */
    public function setJobGroupName(string $jobGroupName): AbstractJobInterface
    {
        $this->jobGroupName = $jobGroupName;
        return $this;
    }

    /**
     * @return string
     */
    public function getJobStatus(): string
    {
        return $this->jobStatus;
    }

    /**
     * @param string $jobStatus
     * @return AbstractJobInterface
     */
    public function setJobStatus(string $jobStatus): AbstractJobInterface
    {
        $this->jobStatus = $jobStatus;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return AbstractJobInterface
     */
    public function setPriority(int $priority): AbstractJobInterface
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData(mixed $data): void
    {
        $this->data = $data;
    }

}