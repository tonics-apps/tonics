<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Commands\Scheduler;

use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * The ScheduleManager is nothing more than a class that encapsulate a specific set of commands that should be run on schedule,
 * for example, a schedule command that cleans up log, deletes inactive users, etc. Each subset of this command should also...
 * implements the ConsoleCommand Interface.
 *
 * <br>
 * Note: It is not like the ScheduleManager is responsible for scheduling the commands, you'll still have to schedule the command in systemD or CronJob.
 * The only thing the ScheduleManager Class does is to register the commands and control how they are executed in order.
 * For example, if you have a scheduleCommand with a signature of "send:email", then you can call it in cron or systemD like so:
 *
 * <code>
 * php bin/console --run --schedule=send:email
 * </code>
 *
 *  <br>
 * or you can run everything at once using:
 *
 * <br>
 *
 * <code>
 * php bin/console --run --schedule
 * </code>
 *
 * <br>
 * Tip: It is preferable not to run every schedule command at once since they would process synchronously,
 * the best way is to add them one at a time in cron!
 *
 * Class ScheduleManager
 * @package App\Commands\Scheduler
 */
class ScheduleManager implements ConsoleCommand
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
     */
    public function run(array $commandOptions): void
    {
        $registrars = $this->registerToBeScheduled();
        foreach ($registrars as $registrar) {
            $this->infoMessage("running {$commandOptions['--schedule']} in schedule");
            /**
             * @var $registrar ConsoleCommand
             */
            if ($registrar instanceof ConsoleCommand) {
                // want to run all the registrars schedule
                if (empty($commandOptions['--schedule'])) {
                    $registrar->run($commandOptions);
                    $this->successMessage("running {$commandOptions['--schedule']} completed");
                } else {
                    // want to run the $registrar one at a time
                    if ($registrar->required() === (array)$commandOptions['--schedule']) {
                        $registrar->run($commandOptions);
                        $this->successMessage("running {$commandOptions['--schedule']} completed");
                        break;
                    }
                }
            }
        }
    }

    /**
     * @return string[]
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function registerToBeScheduled(): array
    {
        return container()->resolveMany([
            //
        ]);
    }
}