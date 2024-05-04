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

namespace App\Modules\Core\Library\JobSystem;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;

class AbstractJobInterface
{
    use ConsoleColor;

    private string $jobName = '';
    private mixed $jobParent = null;
    private string $jobStatus = Job::JobStatus_Queued;
    private string $jobStatusAfterJobHandled = Job::JobStatus_Processed;
    private array $chains = [];
    private int $priority = Scheduler::PRIORITY_URGENT;
    private mixed $data = null;
    private string $jobInfoMessage = '';

    private ?int $retryAfter = null;
    private ?int $maxAttempts = 30;

    private ?AbstractJobInterface $parentObject = null;

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
     * If chains is empty, meaning there are no nested or child job
     * @return bool
     */
    public function chainsEmpty(): bool
    {
        return empty($this->chains);
    }

    /**
     * If chains is not empty, meaning there are nested or child job
     * @return bool
     */
    public function chainsIsNotEmpty(): bool
    {
        return !empty($this->chains);
    }


    /**
     * @param AbstractJobInterface $childJob
     * @return AbstractJobInterface
     */
    protected function addJobToChain(AbstractJobInterface $childJob): AbstractJobInterface
    {
        $this->chains[] = $childJob;
        return $this;
    }



    /**
     * Adds the job relationships recursively based on the $jobs array structure.
     *
     * <br>
     * Here is an example:
     *
     * ```
     *     $jobs = [
     *         [
     *             'job' => new Job1(),
     *             'children' => [
     *                 [
     *                     'job' => new Job2(),
     *                     'children' => []
     *                 ],
     *                 [
     *                     'job' => new Job3(),
     *                     'children' => []
     *                 ],
     *             ]
     *         ],
     *         [
     *             'job' => new Job4(),
     *             'children' => [
     *                 [
     *                     'job' => new Job5(),
     *                     'children' => []
     *                 ],
     *                 [
     *                     'job' => new Job6(),
     *                     'children' => []
     *                 ]
     *             ]
     *         ]
     *     ];
     *
     *     ...->addChains($jobs);
     *```
     *
     * // The job relationships will be processed in the following order:
     *      1. Job1
     *         1.1 Job2
     *         1.2 Job3
     *      2. Job4
     *         2.1 Job5
     *         2.2 Job6
     *
     * <br>
     *
     * Keep in mind since `Job2` and `Job3` are both the child of `Job1` they can be processed concurrently if the transporter has that option, same applies to
     * `Job5` and `Job6`, and the same even applies to `Job1` and `Job4` (They are both separate parent).
     *
     * If you hae multiple parents like in the example above, you can enqueue them like so:
     *
     * ```
     * $chains = $jobInterface->addChains($jobs);
     * try {
     *  foreach ($chains as $chain){
     *      jobs()->enqueue($chain['job']); // or for TonicsCloud // TonicsCloudActivator::getJobQueue()->enqueue($chain['job']);
     *  }
     *  } catch (\Throwable $exception){
     *      // Log Exception or Whatever
     *  }
     * ```
     * @param array $jobs
     * An array representing the job relationships.
     * Each element should have a 'job' property for the job instance
     * and a 'children' property for an array of child jobs.
     * @param AbstractJobInterface|null $parentJob
     * The parent job object. Default is null for top-level jobs.
     * @param null $defaultData adds the defaultData to job if job doesn't have data
     * @return array
     * An array of the representations arranged, then you can just loop the root, get the parent job, and enqueue that,
     * depending on your transporter, the enqueue should hande the handling of the childObjects
     * @throws \Exception
     */
    public static function addChains(array $jobs, ?AbstractJobInterface $parentJob = null, $defaultData = null): array
    {
        foreach ($jobs as $job) {
            /** @var AbstractJobInterface $job */
            if (!isset($job['job'])){
                throw new \Exception("The job property/key is missing");
            }

            if (!($job['job'] instanceof AbstractJobInterface)){
                throw new \Exception("The job value is not an instance of AbstractJobInterface");
            }

            $jobObject = $job['job'];
            $children = $job['children'] ?? null;

            if ($parentJob !== null) {
                $parentJob->addJobToChain($jobObject);
                $jobObject->setParentObject($parentJob);
            }

            if (empty($jobObject->getData())){
                $jobObject->setData($defaultData);
            }

            if (!empty($children)) {
                self::addChains($children, $jobObject, $defaultData);
            }
        }

        return $jobs;
    }

    /**
     * @return array
     */
    public function getChains(): array
    {
        return $this->chains;
    }

    /**
     * @param array $chains
     * @return $this
     */
    public function setChains(array $chains): static
    {
        $this->chains = $chains;
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
     * @return mixed
     */
    public function getJobParent(): mixed
    {
        return $this->jobParent;
    }

    /**
     * @param mixed $jobParent
     */
    public function setJobParent(mixed $jobParent): void
    {
        $this->jobParent = $jobParent;
    }

    /**
     * @return AbstractJobInterface|null
     */
    public function getParentObject(): ?AbstractJobInterface
    {
        return $this->parentObject;
    }

    /**
     * @param AbstractJobInterface|null $parentObject
     * @return AbstractJobInterface
     */
    public function setParentObject(?AbstractJobInterface $parentObject): AbstractJobInterface
    {
        $this->parentObject = $parentObject;
        return $this;
    }

    /**
     * Status to set job after the completion of the handle method, by default it would be set to processed
     * @return string
     */
    public function getJobStatusAfterJobHandled(): string
    {
        return $this->jobStatusAfterJobHandled;
    }

    /**
     * @param string $jobStatusAfterJobHandled
     * @return AbstractJobInterface
     */
    public function setJobStatusAfterJobHandled(string $jobStatusAfterJobHandled): AbstractJobInterface
    {
        $this->jobStatusAfterJobHandled = $jobStatusAfterJobHandled;
        return $this;
    }

    /**
     * If null, it would default to 10 seconds
     * @return int|null
     */
    public function getRetryAfter(): ?int
    {
        if ($this->retryAfter === null){
            return Scheduler::everySecond(10);
        }
        return $this->retryAfter;
    }

    /**
     * @param int|null $retryAfter
     * @return AbstractJobInterface
     */
    public function setRetryAfter(?int $retryAfter = null): AbstractJobInterface
    {
        $this->retryAfter = $retryAfter;
        return $this;
    }

    /**
     * If timeout is null, it defaults to 5 minutes
     * @return int|null
     */
    public function getMaxAttempts(): ?int
    {
        return $this->maxAttempts;
    }

    /**
     * @param int $maxAttempts
     * @return AbstractJobInterface
     */
    public function setMaxAttempts(int $maxAttempts): AbstractJobInterface
    {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    /**
     * @return string
     */
    public function getJobInfoMessage(): string
    {
        return $this->jobInfoMessage;
    }

    /**
     * @param string $jobInfoMessage
     * @return AbstractJobInterface
     */
    public function setJobInfoMessage(string $jobInfoMessage): AbstractJobInterface
    {
        $this->jobInfoMessage = $jobInfoMessage;
        return $this;
    }

}