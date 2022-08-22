<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands\Scheduler;

use App\Modules\Core\Commands\OnStartUpCLI;
use App\Modules\Core\Commands\UpdateMechanism\AutoUpdate;
use App\Modules\Core\Commands\UpdateMechanism\Updates;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Schedules\AutoUpdates;
use App\Modules\Core\Schedules\DiscoverUpdates;
use App\Modules\Core\Schedules\JobManager;
use App\Modules\Core\Schedules\PurgeOldSession;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Throwable;

/**
 * The ScheduleManager is nothing more than a class that encapsulate a specific set of commands that should be run on schedule,
 * for example, a schedule command can update, cleans up log, deletes inactive users, etc.
 *
 * RUN: `php bin/console --run --schedule` to enqueue the core schedule events and start working on all schedule events
 *
 *
 * Class ScheduleManager
 * @package App\Commands\Scheduler
 */
class ScheduleManager implements ConsoleCommand, HandlerInterface
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--run",
            "--schedule"
        ];
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $helper = helper();
        $this->coreSchedules();
        $this->successMessage('Scheduled work mode ON, started with a memory of ' . $helper->formatBytes(memory_get_usage()));
        $this->startWorkingSchedule();
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function coreSchedules(): void
    {
        $coreScheduleEvents = container()->resolveMany([
            PurgeOldSession::class,
            DiscoverUpdates::class,
            AutoUpdates::class,
        ]);

        foreach ($coreScheduleEvents as $scheduleEvent){
            $this->infoMessage("Enqueuing {$scheduleEvent->getName()} in schedule");
            try {
                schedule()->enqueue($scheduleEvent);
            }catch (\Exception $exception){
                $this->errorMessage("An error occurred while enqueuing schedule event");
                $this->errorMessage($exception->getMessage());
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function startWorkingSchedule()
    {
        try {
            schedule()->runSchedule();
        }catch (Throwable $exception){ // catch most exception or error...
            $this->errorMessage($exception->getMessage());
            $this->errorMessage($exception->getTraceAsString());
        }
    }

    public function handleEvent(object $event): void
    {
        /** @var $event OnStartUpCLI */
        $event->addClass(get_class($this));
    }
}