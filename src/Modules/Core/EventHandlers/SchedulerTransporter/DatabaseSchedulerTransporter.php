<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers\SchedulerTransporter;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\OnAddSchedulerTransporter;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\SchedulerTransporterInterface;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsHelpers\TonicsHelpers;
use Throwable;

class DatabaseSchedulerTransporter implements SchedulerTransporterInterface, HandlerInterface
{
    use ConsoleColor;

    private ?TonicsHelpers $helper = null;

    public function name(): string
    {
        return 'Database';
    }

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddSchedulerTransporter */
        $event->addSchedulerTransporter($this);
    }

    public function getTable(): string
    {
        return Tables::getTable(Tables::SCHEDULER);
    }

    public function updateKeyOnUpdate(): array
    {
        return ['schedule_priority', 'schedule_parallel', 'schedule_data', 'schedule_every', 'schedule_ticks'];
    }

    /**
     * @param AbstractSchedulerInterface $scheduleObject
     * @return void
     * @throws \Exception
     */
    public function enqueue(AbstractSchedulerInterface $scheduleObject): void
    {
        if ($scheduleObject->chainsEmpty()) {
            $insert = $this->getToInsert($scheduleObject);
        } else {
            $insertChild = $this->getToInsert($scheduleObject);
            $insertChild['schedule_parent_name'] = null;
            $insert = [$insertChild];
            foreach ($this->recursivelyGetChildObject($scheduleObject) as $child) {
                $insertChild = $this->getToInsert($child);
                $insertChild['schedule_parent_name'] = $child->getParent()?->getName();
                $insert[] = $insertChild;
            }
        }

        db(onGetDB: function ($db) use ($insert) {
            $db->insertOnDuplicate($this->getTable(), $insert, $this->updateKeyOnUpdate());
        });

    }

    public function getToInsert(AbstractSchedulerInterface $scheduleObject): array
    {
        return [
            'schedule_name' => $scheduleObject->getName(),
            'schedule_priority' => $scheduleObject->getPriority(),
            'schedule_parallel' => $scheduleObject->getParallel(),
            // 'schedule_data' => json_encode(get_class($scheduleObject)),
            'schedule_data' => json_encode([
                'data' => $scheduleObject->getData(),
                'class' => get_class($scheduleObject)]),
            // when a scheduleObject has a parent,
            // then schedule_every should be 0 since it is tied to a parent
            // (it has no business in scheduling anything, it is directly called after parent)
            'schedule_every' => (is_null($scheduleObject->getParent())) ? $scheduleObject->getEvery() : 0,
        ];
    }

    public function recursivelyGetChildObject(AbstractSchedulerInterface $scheduleObject): \Generator
    {
        foreach ($scheduleObject->getChains() as $chain) {
            /**@var AbstractSchedulerInterface $chain */
            yield $chain;
            if ($chain->chainsEmpty() === false) {
                yield from $this->recursivelyGetChildObject($chain);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function runSchedule(): void
    {
        $this->helper = helper();
        while (true) {
            if (AppConfig::isMaintenanceMode()) {
                $this->infoMessage("Site in Maintenance Mode...Sleeping");
                usleep(5000000); # Sleep for 5 seconds
                continue;
            }
            $schedules = $this->getNextScheduledEvent();
            if (empty($schedules)) {
                # While the schedule event is empty, we sleep for a 0.1, this reduces the CPU usage, thus giving the CPU the chance to do other things
                usleep(100000);
                continue;
            }
            foreach ($schedules as $schedule) {
                $scheduleData = json_decode($schedule->schedule_data);
                $scheduleClass = $scheduleData->class ?? $scheduleData;
                if ($this->helper->classImplements($scheduleClass, [ScheduleHandlerInterface::class])) {
                    /** @var ScheduleHandlerInterface|AbstractSchedulerInterface $scheduleObject */
                    $scheduleObject = new $scheduleClass;
                    $scheduleObject->setName($schedule->schedule_name);
                    $scheduleObject->setData($scheduleData->data ?? []);
                    if (isset($schedule->_children)) {
                        $this->recursivelyCollateScheduleObject($schedule->_children, $scheduleObject);
                    }
                    $this->infoMessage("Running $schedule->schedule_name Scheduled Event");
                    $this->tick($schedule, $scheduleObject);

                    $this->helper->fork(
                        $schedule->schedule_parallel,
                        onChild: function () use ($schedule, $scheduleObject) {
                            cli_set_process_title("$schedule->schedule_name Scheduled Event");
                            try {
                                $scheduleObject->handle();
                                exit(0); # Success if not exception is thrown
                            } catch (Throwable $exception) {
                                $this->errorMessage($exception->getMessage());
                                $this->errorMessage($exception->getTraceAsString());
                                exit(1); # Failed
                            }
                        },
                        onForkError: function () {
                            // handle the fork error here for the parent, this is because when a fork error occurs
                            // it propagates to the parent which abruptly stop the script execution
                            $this->errorMessage("Unable to Fork");
                        }
                    );
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function getNextScheduledEvent(): array
    {
        $table = Tables::getTable(Tables::SCHEDULER);

        $data = null;
        db(onGetDB: function ($db) use ($table, &$data) {
            $data = $db->run("
        WITH RECURSIVE scheduler_recursive AS 
	( SELECT schedule_id, schedule_name, schedule_parent_name, schedule_priority, schedule_parallel, schedule_data, schedule_ticks, schedule_next_run
      FROM $table WHERE schedule_parent_name IS NULL AND NOW() >= schedule_next_run
      UNION ALL
      SELECT tsf.schedule_id, tsf.schedule_name, tsf.schedule_parent_name, tsf.schedule_priority, tsf.schedule_parallel, tsf.schedule_data, tsf.schedule_ticks, tsf.schedule_next_run
      FROM $table as tsf JOIN scheduler_recursive as ts ON ts.schedule_name = tsf.schedule_parent_name
      ) 
     SELECT * FROM scheduler_recursive;
        ");
        });

        $schedules = $this->helper->generateTree(['parent_id' => 'schedule_parent_name', 'id' => 'schedule_name'], $data);
        usort($schedules, function ($id1, $id2) {
            return $id1->schedule_priority < $id2->schedule_priority;
        });

        return $schedules;
    }

    /**
     * @param $schedule
     * @param $scheduleObject
     * @return void
     * @throws \Exception
     */
    public function tick($schedule, $scheduleObject): void
    {
        $update = $this->getToInsert($scheduleObject);
        $update['schedule_ticks'] = $schedule->schedule_ticks + 1;
        db(onGetDB: function ($db) use ($update) {
            $db->insertOnDuplicate($this->getTable(), $update, $this->updateKeyOnUpdate());
        });
    }

    /**
     * @param $schedules
     * @param AbstractSchedulerInterface|null $parent
     * @return void
     */
    public function recursivelyCollateScheduleObject($schedules, AbstractSchedulerInterface $parent = null): void
    {
        foreach ($schedules as $schedule) {
            $scheduleData = json_decode($schedule->schedule_data);
            $scheduleClass = $scheduleData->class ?? $scheduleData;
            if ($this->helper->classImplements($scheduleClass, [ScheduleHandlerInterface::class])) {
                $scheduleObject = new $scheduleClass;
                $scheduleObject->setName($schedule->schedule_name);
                /** @var $scheduleObject AbstractSchedulerInterface */
                $scheduleObject->setParent($parent);
                $scheduleObject->setData($scheduleData->data ?? []);
                if (isset($schedule->_children)) {
                    $this->recursivelyCollateScheduleObject($schedule->_children, $scheduleObject);
                }
            }
        }
    }
}