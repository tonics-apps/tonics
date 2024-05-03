<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers\JobQueueTransporter;

use App\Apps\TonicsCloud\Commands\CloudJobQueueManager;
use App\Apps\TonicsCloud\Events\OnAddCloudJobQueueTransporter;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Library\AbstractJobOnStartUpCLIHandler;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\JobSystem\JobTransporterInterface;
use App\Modules\Core\Library\SharedMemory;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Throwable;

class DatabaseCloudJobQueueTransporter extends AbstractJobOnStartUpCLIHandler implements JobTransporterInterface, HandlerInterface
{
    use ConsoleColor;

    private int $maxForks = 3000;
    private int $forkCount  = 0;
    private array $pIDS  = [];
    private ?TonicsHelpers $helper = null;
    private ?SharedMemory $sharedMemory = null;

    private int $perJob = 5;

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddCloudJobQueueTransporter */
        $event->addJobTransporter($this);
    }

    public function getTable(): string
    {
        return TonicsCloudActivator::getTable(TonicsCloudActivator::TONICS_CLOUD_JOBS_QUEUE);
    }


    public function name(): string
    {
        return 'Database';
    }


    /**
     * @param AbstractJobInterface $scheduleObject
     * @return \Generator
     */
    public function recursivelyGetChildObject(AbstractJobInterface $scheduleObject): \Generator
    {
        foreach ($scheduleObject->getChains() as $chain) {
            /**@var AbstractJobInterface $chain */
            yield $chain;
            if ($chain->chainsEmpty() === false) {
                yield from $this->recursivelyGetChildObject($chain);
            }
        }
    }

    /**
     * @param AbstractJobInterface $jobEvent
     * @param TonicsQuery $db
     * @param array $inserts
     * @return ?\stdClass
     */
    public function insertJobDatabase(AbstractJobInterface $jobEvent, TonicsQuery $db, array $inserts): ?\stdClass
    {
        $returning = $db->Q()->InsertReturning($this->getTable(), $inserts, TonicsCloudActivator::$TABLES[TonicsCloudActivator::TONICS_CLOUD_JOBS_QUEUE], 'job_queue_id');
        $jobEvent->setData($returning);
        return $returning;
    }

    /**
     * @param AbstractJobInterface $jobEvent
     * @param callable|null $beforeEnqueue
     * @param callable|null $afterEnqueue
     * @return void
     * @throws \Exception
     */
    public function enqueue(AbstractJobInterface $jobEvent, callable $beforeEnqueue = null, callable $afterEnqueue = null): void
    {
        $this->helper = helper();
        $inserts = $this->getToInsert($jobEvent);
        if ($beforeEnqueue) {
            $beforeEnqueue($inserts);
        }

        $parentEnqueued = null;
        db(onGetDB: function (TonicsQuery $db) use ($jobEvent, &$parentEnqueued, &$inserts){
            $db->beginTransaction();

            $inserts['job_queue_parent_job_id'] = null;
            $parentEnqueued = $this->insertJobDatabase($jobEvent, $db, $inserts);

            if ($jobEvent->chainsIsNotEmpty()){
                /** @var AbstractJobInterface $child */
                foreach ($this->recursivelyGetChildObject($jobEvent) as $child) {
                   $this->insertJobDatabase($child, $db, $this->getToInsert($child));
                }
            }

            $db->commit();
        });

        if($afterEnqueue){
            $afterEnqueue($parentEnqueued);
        }
    }


    /**
     * @param AbstractJobInterface $jobEvent
     * @return array
     */
    public function getToInsert(AbstractJobInterface $jobEvent): array
    {
        return [
            'job_queue_parent_job_id' => $jobEvent->getParentObject()?->getData()?->job_queue_id,
            'job_queue_name' => $jobEvent->getJobName() ?: $this->helper->getObjectShortClassName($jobEvent),
            'job_queue_status' => $jobEvent->getJobStatus(),
            'job_queue_priority' => $jobEvent->getPriority(),
            'job_attempts' => $jobEvent->getMaxAttempts(),
            'job_queue_data' => json_encode([
                    'data' => $jobEvent->getData(),
                    'class' => get_class($jobEvent)]
            )
        ];
    }

    /**
     * @throws \Exception
     */
    public function runJob(): void
    {

        $this->helper = helper();
        $this->sharedMemory = new SharedMemory(CloudJobQueueManager::masterKey(), CloudJobQueueManager::semaphoreID(), CloudJobQueueManager::sharedMemorySize());

        $this->run(function (){
            $jobs = $this->getNextJobs();
            if (empty($jobs)) {
                # While the job event is empty, we sleep for a 0.4s, this reduces the CPU usage, thus giving the CPU the chance to do other things
                usleep(400000);
                return;
            }

            /**
             * As long as we haven't reached maxForks and there are jobs to process, it would keep forking,
             * the good thing about this is that we now have the luxury of handle multiple jobs at a go in batches,
             * isn't that awesome, and to put an icing on the cake, it is fork process safe.
             */
            foreach ($jobs as $job){
                $this->helper->fork(
                    onChild: function () use ($job) {
                        try {
                            $this->prepJobHandle($job);
                            exit(0); # Success if no exception is thrown
                        } catch (Throwable $exception) {
                            $this->errorMessage($exception->getMessage());
                            exit(1); # Failed
                        }
                    },
                    onParent: function ($pid){
                        $this->pIDS[] = $pid; # store the child pid
                        // here is where we limit the number of forked process,
                        // if the maxed forked has been reached, we wait for any child fork to exit,
                        // once it does, we remove the pid that exited from the list (queue) so another one can come in.
                        // this effectively limit too many processes from forking
                        if (count($this->pIDS) >= $this->maxForks) {
                            $pid = pcntl_waitpid(-1, $status);
                            unset($this->pIDS[$pid]); // Remove PID that exited from the list
                            $this->infoMessage("Maximum Number of {$this->maxForks} TonicsCloud JobQueue Forks Reached, Opening For New Fork");

                            $pIDSCountBeforeGC = count($this->pIDS);
                            $this->infoMessage("Garbage Collecting pIDS To See if We Can Have More Forks At a Go, pIDS Before GC Count is: $pIDSCountBeforeGC");

                            $this->garbageCollectPID();

                            $pIDSCountAfterGC = count($this->pIDS);
                            $this->infoMessage("pIDS After GC Count is: $pIDSCountAfterGC");
                        }
                    },
                    onForkError: function () {
                        // handle the fork error here for the parent, this is because when a fork error occurs
                        // it propagates to the parent which abruptly stop the script execution
                        $this->errorMessage("Unable to Fork");
                    }
                );
            }

            unset($jobs);
        }, shutDown: function (){
            $this->sharedMemory->cleanSharedMemory();
        });
    }

    /**
     * Clean PIDS that are no longer running in $this->pIDS, this way, we can run more processes as long as we haven't reached the maxForks limit
     * @return void
     */
    public function garbageCollectPID(): void
    {
        foreach ($this->pIDS as $key => $pID) {
            if (posix_getpgid($pID) === false){
                unset($this->pIDS[$key]);
            }
        }
    }

    /**
     * The way this works or is supposed to work is retrieve a job from the queue_table where the job's status is 'queued' and it either has no parent job or its parent job has a status of 'processed'.
     * The query prioritizes the jobs based on their priority values in descending order, and if multiple jobs have the same priority,
     * it selects the one with the lowest job ID (FIFO or on a first come, first served basis).
     *
     * <br>
     * The condition `(j.retry_after IS NULL OR j.retry_after <= NOW())` checks if the retry_after value is either NULL or has passed the current timestamp,
     * indicating that the job can be processed.
     *
     * <br>
     * The condition `j.job_attempts > 0` checks if the job can still be attempted
     *
     * <br>
     * Finally, it limits the result to only one job.
     *
     * @throws \Exception
     */
    public function getNextJobs(): mixed
    {
        return $this->sharedMemory->ensureAtomicity(function (SharedMemory $sharedMemory){
            $nextJobs = null;
            db(onGetDB: function (TonicsQuery $db) use (&$nextJobs){
                $table = $this->getTable();
                $nextJobs = $db->run(<<<SQL
SELECT j.*
FROM $table j
WHERE j.job_queue_status = ?
  AND (j.job_queue_parent_job_id IS NULL OR j.job_queue_parent_job_id NOT IN (
      SELECT job_queue_id
      FROM $table
      WHERE job_queue_status != ?
  ))
  AND (j.job_retry_after IS NULL OR j.job_retry_after <= NOW())
  AND j.job_attempts > 0
ORDER BY j.job_queue_priority DESC, j.job_queue_id ASC
LIMIT ?;
SQL, Job::JobStatus_Queued, Job::JobStatus_Processed, $this->getPerJob());

                # Since we have gotten access to semaphore, let's use this opportunity to quickly update the job status
                # this completely prevents different jobs from stepping on each other toes for concurrent job
                if (!empty($nextJobs)){

                    # Job In_Progress
                    $updates = [];
                    foreach ($nextJobs as $nextJob){
                        $retryAfter = null;
                        $this->getJobObject($nextJob, function (AbstractJobInterface $jobObject) use ($nextJob, &$retryAfter) {
                            $retryAfter = $this->retryAfter($jobObject);
                        });

                        $updates[] = [
                            'job_queue_id' => $nextJob->job_queue_id, 'job_queue_status' => Job::JobStatus_InProgress,
                            'job_queue_priority' => $nextJob->job_queue_priority, 'job_attempts' => $nextJob->job_attempts,
                            'job_retry_after' => $retryAfter, 'job_queue_name' => $nextJob->job_queue_name
                        ];
                    }

                    $db->Q()->InsertOnDuplicate($table, $updates, ['job_queue_status', 'job_retry_after']);
                }
            });

            # Since we are done, we should remove semaphore, if we do not do this, it would be impossible to
            # kill child process which in theory might have completed but since its semaphore is not released, it isn't considered completed
            # so by removing it, the child can close with ease or the respective SIGCHILD signal handler can handle the child zombie cleaning
            $sharedMemory->detachSemaphore();
            $sharedMemory->removeSemaphore();

            return $nextJobs;
        });
    }

    /**
     * @param $job
     * @return void
     * @throws \Exception
     */
    public function prepJobHandle($job): void
    {

        # Name CLI Process
        cli_set_process_title("$job->job_queue_name TonicsCloud JobQueue Event");

        $this->infoMessage("Running TonicsCloud job queue $job->job_queue_name with an id of $job->job_queue_id");

        # Decrement Priority, Fail or No Fail
        $priority = ($job->job_queue_priority > 0) ? $job->job_queue_priority - 1 : $job->job_queue_priority;
        $attempts = ($job->job_attempts > 0) ? $job->job_attempts - 1 : $job->job_attempts;

        try {
            $jobObject = $this->handleIndividualJob($job);
            db(onGetDB: function (TonicsQuery $db) use ($attempts, $jobObject, $priority, $job) {
                // we can't just set the status to success, it would only become processed if the $job handle says so,
                // this way, we can go back and retry albeit with a low priority, if the priority is 0, we stop trying
               // $update = ['job_queue_status' => 'processed', 'job_queue_priority' => $priority, 'job_attempts' => $attempts];
                $update = ['job_queue_status' => $jobObject->getJobStatusAfterJobHandled(), 'job_queue_priority' => $priority, 'job_attempts' => $attempts];
                $db->Q()->FastUpdate($this->getTable(), $update, db()->WhereEquals('job_queue_id', $job->job_queue_id));
            });
        } catch (\Throwable $exception) {
            $this->errorMessage("TonicsCloud JobQueue $job->job_queue_name failed, with an id of $job->job_queue_id");
            db(onGetDB: function (TonicsQuery $db) use ($attempts, $priority, $job) {
                $update = ['job_queue_status' => Job::JobStatus_Failed, 'job_queue_priority' => $priority, 'job_attempts' => $attempts];
                $db->Q()->FastUpdate($this->getTable(), $update, db()->WhereEquals('job_queue_id', $job->job_queue_id));
            });
            throw new \Exception($exception->getMessage() . $exception->getTraceAsString());
        }

    }

    /**
     * @param AbstractJobInterface $jobObject
     * @return string
     */
    public function retryAfter(AbstractJobInterface $jobObject): string
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $this->helper->date());
        $dateTime->modify('+' . $jobObject->getRetryAfter() . ' seconds');
        // Get the updated timestamp
        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @param $job
     * @return AbstractJobInterface|JobHandlerInterface|null
     * @throws \Exception
     */
    public function handleIndividualJob($job): JobHandlerInterface|AbstractJobInterface|null
    {
        $jobObj = null;
        $this->getJobObject($job, onGetJobObject: function ($jobObject) use (&$jobObj){
            $jobObj = $jobObject;
            $jobObject->handle();
        });

        return $jobObj;
    }

    /**
     * @param $job
     * @param callable|null $onGetJobObject
     * @return AbstractJobInterface|JobHandlerInterface|null
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getJobObject($job, callable $onGetJobObject = null): JobHandlerInterface|AbstractJobInterface|null
    {
        $jobData = json_decode($job->job_queue_data);
        if (isset($jobData->class) && is_a($jobData->class, AbstractJobInterface::class, true)) {
            /** @var AbstractJobInterface $jobObject */
            $jobObject = container()->get($jobData->class);
            $jobObject->setData($jobData->data ?? []);
            $jobObject->setJobParent($jobData->job_queue_parent_job_id ?? null);
            if ($jobObject instanceof JobHandlerInterface) {
                if ($onGetJobObject){
                    $onGetJobObject($jobObject);
                }
                return $jobObject;
            }
        }

        return null;
    }

    public function isStatic(): bool
    {
        return false;
    }

    /**
     * @return int
     */
    public function getPerJob(): int
    {
        return $this->perJob;
    }

    /**
     * @param int $perJob
     */
    public function setPerJob(int $perJob): void
    {
        $this->perJob = $perJob;
    }
}