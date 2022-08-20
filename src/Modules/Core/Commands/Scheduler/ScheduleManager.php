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

use App\Modules\Core\Commands\UpdateMechanism\AutoUpdate;
use App\Modules\Core\Commands\UpdateMechanism\Updates;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Schedules\AutoUpdates;
use App\Modules\Core\Schedules\DiscoverUpdates;
use App\Modules\Core\Schedules\JobManager;
use App\Modules\Core\Schedules\PurgeOldSession;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * The ScheduleManager is nothing more than a class that encapsulate a specific set of commands that should be run on schedule,
 * for example, a schedule command can update, cleans up log, deletes inactive users, etc.
 *
 * RUN: `php bin/console --run --schedule` to enqueue the schedule events
 *
 * RUN:  `php bin/console --run=work --schedule` to enqueue and start working on all scheduled events
 *
 * Class ScheduleManager
 * @package App\Commands\Scheduler
 */
class ScheduleManager implements ConsoleCommand
{
    use ConsoleColor;

    private $helper = null;

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
        $this->helper = helper();
        $this->coreSchedules();
        if ($commandOptions['--run'] === 'work'){
            $this->successMessage('Scheduled work mode ON');
            $this->infoMessage('Listening to new scheduled events');
            $this->startWorkingSchedule();
        }
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function coreSchedules(): void
    {
        $coreScheduleEvents = container()->resolveMany([
            JobManager::class,
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

    public function startWorkingSchedule()
    {
        $this->getNextScheduledEvent();
        while (true){
            $this->infoMessage('Schedule...' . $this->helper->formatBytes(memory_get_usage()));
            sleep(1);
        }
    }

    /**
     * @throws \Exception
     */
    public function getNextScheduledEvent()
    {
        $table = Tables::getTable(Tables::SCHEDULER);
        $data = db()->run("
        WITH RECURSIVE scheduler_recursive AS 
	( SELECT schedule_id, schedule_name, schedule_parent_name, schedule_priority, schedule_parallel, schedule_data, schedule_ticks, schedule_ticks_max, schedule_next_run
      FROM $table WHERE schedule_parent_name IS NULL AND NOW() >= schedule_next_run
      UNION ALL
      SELECT tsf.schedule_id, tsf.schedule_name, tsf.schedule_parent_name, tsf.schedule_priority, tsf.schedule_parallel, tsf.schedule_data, tsf.schedule_ticks, tsf.schedule_ticks_max, tsf.schedule_next_run
      FROM $table as tsf JOIN scheduler_recursive as ts ON ts.schedule_name = tsf.schedule_parent_name
      ) 
     SELECT * FROM scheduler_recursive;
        ");

        $categories = $this->helper->generateTree(['parent_id' => 'schedule_parent_name', 'id' => 'schedule_name'], $data);
        dd($categories);
    }
}