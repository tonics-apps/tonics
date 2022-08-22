<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Schedules;

use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SchedulerSystem\AbstractSchedulerInterface;
use App\Modules\Core\Library\SchedulerSystem\ScheduleHandlerInterface;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\Tables;

class PurgeOldSession extends AbstractSchedulerInterface implements ScheduleHandlerInterface
{
    use ConsoleColor;

    public function __construct()
    {
        $this->setName('Core_PurgeOldSession');
        $this->setPriority(Scheduler::PRIORITY_LOW);
        $this->setEvery(Scheduler::everyHour(3));
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $table = Tables::getTable(Tables::SESSIONS);
        $db = db(true);
        $total = $db->row("SELECT COUNT(*) AS total FROM $table WHERE `updated_at` <= NOW()");
        $chunksToDeleteAtATime = 1000;

        if (isset($total->total)){
            $total = $total->total;
            $noOfTimesToLoop = ceil($total / $chunksToDeleteAtATime);
            for ($i = 1; $i <= $noOfTimesToLoop; $i++) {
                db()->run("DELETE FROM $table WHERE `updated_at` <= NOW() LIMIT $chunksToDeleteAtATime");
            }
        }
    }
}