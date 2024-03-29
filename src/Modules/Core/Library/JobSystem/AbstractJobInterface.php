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
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;

class AbstractJobInterface
{
    use ConsoleColor;

    private string $jobName = '';
    private ?int $jobParentID = null;
    private string $jobStatus = Job::JobStatus_Queued;
    private int $priority = Scheduler::PRIORITY_MEDIUM;
    private mixed $data = null;

    /**
     * @return string
     */
    public function getJobName(): string
    {
        return $this->jobName;
    }

    /**
     * @param string $jobName
     * @return AbstractJobInterface
     */
    public function setJobName(string $jobName): AbstractJobInterface
    {
        $this->jobName = $jobName;
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
     * @return array
     */
    public function getDataAsArray(): array
    {
        return (array)$this->data;
    }

    /**
     * @return object
     */
    public function getDataAsObject(): object
    {
        return (object)$this->data;
    }

    /**
     * @param mixed $data
     * @return AbstractJobInterface
     */
    public function setData(mixed $data): AbstractJobInterface
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getJobParentID(): ?int
    {
        return $this->jobParentID;
    }

    /**
     * @param int|null $jobParentID
     */
    public function setJobParentID(?int $jobParentID): void
    {
        $this->jobParentID = $jobParentID;
    }

}