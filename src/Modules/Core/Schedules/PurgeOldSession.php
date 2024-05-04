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
        db(onGetDB: function ($db){
            $table = Tables::getTable(Tables::SESSIONS);
            $total = $db->row("SELECT COUNT(*) AS total FROM $table WHERE `updated_at` <= NOW()");
            $chunksToDeleteAtATime = 1000;
            if (isset($total->total)){
                $total = $total->total;
                $noOfTimesToLoop = ceil($total / $chunksToDeleteAtATime);
                for ($i = 1; $i <= $noOfTimesToLoop; $i++) {
                    $db->run("DELETE FROM $table WHERE `updated_at` <= NOW() LIMIT $chunksToDeleteAtATime");
                }
            }
        });

    }
}