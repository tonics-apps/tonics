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

use App\Modules\Core\Events\OnAddSchedulerTransporter;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\SchedulerTransporterInterface;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DatabaseSchedulerTransporter implements SchedulerTransporterInterface, HandlerInterface
{

    public function name(): string
    {
        return 'Database';
    }

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddSchedulerTransporter */
        $event->addSchedulerTransporter($this);
    }

    /**
     * @throws \Exception
     */
    public function enqueue(AbstractSchedulerInterface $scheduleObject): void
    {
        $table = Tables::getTable(Tables::SCHEDULER);
        $updatesKeyOnUpdate = ['schedule_priority', 'schedule_parallel', 'schedule_data', 'schedule_ticks_max', 'schedule_every'];
        if ($scheduleObject->chainsEmpty()){
            $insert = $this->getToInsert($scheduleObject);
        } else {
            $insertChild = $this->getToInsert($scheduleObject);
            $insertChild['schedule_parent_name'] = null;
            $insert = [$insertChild];
            foreach ($this->recursivelyGetChildObject($scheduleObject) as $child){
                $insertChild = $this->getToInsert($child);
                $insertChild['schedule_parent_name'] = $child->getParent()?->getName();
                $insert[] = $insertChild;
            }
        }
        db()->insertOnDuplicate($table, $insert, $updatesKeyOnUpdate);
    }

    public function getToInsert(AbstractSchedulerInterface $scheduleObject): array
    {
        return [
            'schedule_name' => $scheduleObject->getName(),
            'schedule_priority' => $scheduleObject->getPriority(),
            'schedule_parallel' => $scheduleObject->getParallel(),
            'schedule_data' => json_encode(get_class($scheduleObject)),
            'schedule_ticks_max' => $scheduleObject->getMaxTicks(),
            // when a scheduleObject has a parent,
            // then schedule_every should be 0 since it is tied to a parent
            // (it has no business in scheduling anything, it is directly called after parent)
            'schedule_every' => (is_null($scheduleObject->getParent())) ? $scheduleObject->getEvery() : 0,
        ];
    }

    public function recursivelyGetChildObject(AbstractSchedulerInterface $scheduleObject): \Generator
    {
        foreach ($scheduleObject->getChains() as $chain)
        {
            /**@var AbstractSchedulerInterface $chain*/
            yield $chain;
            if ($chain->chainsEmpty() === false){
                yield from $this->recursivelyGetChildObject($chain);
            }
        }
    }

    public function runSchedule(): void
    {
        // TODO: Implement runSchedule() method.
    }
}