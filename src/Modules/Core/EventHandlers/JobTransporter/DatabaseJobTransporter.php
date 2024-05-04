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

namespace App\Modules\Core\EventHandlers\JobTransporter;

use App\Modules\Core\Commands\Job\JobManager;
use App\Modules\Core\Events\OnAddJobTransporter;
use App\Modules\Core\Library\AbstractJobOnStartUpCLIHandler;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Core\Library\JobSystem\JobTransporterInterface;
use App\Modules\Core\Library\SharedMemory;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Throwable;

class DatabaseJobTransporter extends AbstractJobOnStartUpCLIHandler implements JobTransporterInterface, HandlerInterface
{
    use ConsoleColor;

    private int $maxForks = 10;
    private int $forkCount  = 0;
    private array $pIDS  = [];
    private ?TonicsHelpers $helper = null;
    private ?SharedMemory $sharedMemory = null;

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
        $this->helper = helper();
        $toInsert = $this->getToInsert($jobEvent);
        if ($beforeEnqueue) {
            $beforeEnqueue($toInsert);
        }

        db(onGetDB: function (TonicsQuery $db) use ($afterEnqueue, $toInsert) {
            if ($afterEnqueue){
                $returning = $db->InsertReturning($this->getTable(), $toInsert, Tables::$TABLES[Tables::JOBS], 'job_id');
                $afterEnqueue($returning);
            } else {
                $db->insert($this->getTable(), $toInsert);
            }

        });

    }

    /**
     * @param AbstractJobInterface $jobEvent
     * @return array
     */
    public function getToInsert(AbstractJobInterface $jobEvent): array
    {
        return [
            'job_name' => $jobEvent->getJobName() ?: $this->helper->getObjectShortClassName($jobEvent),
            'job_parent_id' => $jobEvent->getJobParent(),
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
     * @return void
     * @throws \Exception
     */
    public function runJob(): void
    {
       $this->helper = helper();
       $this->sharedMemory = new SharedMemory(JobManager::masterKey(), JobManager::semaphoreID(), JobManager::sharedMemorySize());

        $this->run(function (){
            $job = $this->getNextJob();
            if (empty($job)) {
                # While the job event is empty, we sleep for a 0.2s, this reduces the CPU usage, thus giving the CPU the chance to do other things
                usleep(200000);
                return;
            }

            /**
             * As long as we haven't reached maxForks and there are jobs to process, it would keep forking,
             * the good thing about this is that we now have the luxury of handle multiple jobs at a go in batches,
             * isn't that awesome, and to put an icing on the cake, it is fork process safe.
             */
            $this->helper->fork(
                onChild: function () use ($job) {
                    try {
                        cli_set_process_title("$job->job_name Job Event");
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
                    // this effectively limit
                    if (count($this->pIDS) >= $this->maxForks) {
                        $pid = pcntl_waitpid(-1, $status);
                        unset($this->pIDS[$pid]); // Remove PID that exited from the list
                        $this->infoMessage("Maximum Number of {$this->maxForks} Job Forks Reached, Opening For New Fork");
                    }
                },
                onForkError: function () {
                    // handle the fork error here for the parent, this is because when a fork error occurs
                    // it propagates to the parent which abruptly stop the script execution
                    $this->errorMessage("Unable to Fork");
                }
            );
        }, shutDown: function (){
            $this->sharedMemory->cleanSharedMemory();
        });
    }

    /**
     * This query first selects all rows from the jobs table where the job_status is 'queued'.
     * It then uses a sub-query to select all job_parent_id values from the jobs table that are not NULL.
     *
     * This effectively gets a list of all job_id values for jobs that have child jobs.
     * The outer query then filters out any jobs whose job_id is in the list of job_parent_id values, effectively excluding any jobs that have child jobs.
     * @return mixed|null
     * @throws \Exception
     */
    public function getNextJob(): mixed
    {
        return $this->sharedMemory->ensureAtomicity(function (SharedMemory $sharedMemory){
            $nextJob = null;
            db(onGetDB: function (TonicsQuery $db) use (&$nextJob){
                $table = $this->getTable();
                $nextJob = $db->row(<<<SQL
SELECT * 
FROM $table
WHERE `job_status` = ? AND `job_id` NOT IN (SELECT `job_parent_id` FROM $table WHERE `job_parent_id` IS NOT NULL)
ORDER BY `job_priority` DESC
LIMIT ?
SQL, Job::JobStatus_Queued, 1);

                # Since we have gotten access to semaphore, let's use this opportunity to quickly update the job status
                # this completely prevents different jobs from stepping on each other toes for concurrent job
                if ($nextJob){
                    $this->infoMessage("Running job $nextJob->job_name with an id of $nextJob->job_id");
                    # Job In_Progress
                    $update = ['job_status' => Job::JobStatus_InProgress];
                    $db->Q()->FastUpdate($this->getTable(), $update, db()->WhereEquals('job_id', $nextJob->job_id));
                }
            });

            # Since we are done, we should remove semaphore, if we do not do this, it would be impossible to
            # kill child process which in theory might have completed but since its semaphore is not released, it isn't considered completed
            # so by removing it, the child can close with ease or the respective SIGCHILD signal handler can handle the child zombie cleaning
            $sharedMemory->detachSemaphore();
            $sharedMemory->removeSemaphore();

            return $nextJob;
        });
    }

    /**
     * @param $job
     * @return void
     * @throws \Exception
     */
    public function prepJobHandle($job): void
    {
        try {
            $this->handleIndividualJob($job);
            db(onGetDB: function (TonicsQuery $db) use ($job) {
                $update = ['job_status' => Job::JobStatus_Processed, 'time_completed' => helper()->date()];
                $db->Q()->FastUpdate($this->getTable(), $update, db()->WhereEquals('job_id', $job->job_id));
            });

        } catch (\Throwable $exception) {
            $this->errorMessage("Job $job->job_name failed, with an id of $job->job_id");
            db(onGetDB: function (TonicsQuery $db) use ($job) {
                $update = ['job_status' => Job::JobStatus_Failed];
                $db->Q()->FastUpdate($this->getTable(), $update, db()->WhereEquals('job_id', $job->job_id));
            });
            throw new \Exception($exception->getMessage() . $exception->getTraceAsString());
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