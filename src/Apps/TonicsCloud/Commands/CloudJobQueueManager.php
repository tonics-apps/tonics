<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Apps\TonicsCloud\Commands;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Commands\OnStartUpCLI;
use App\Modules\Core\Events\OnAddConsoleCommand;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SharedMemoryInterface;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

/**
 * RUN: `php bin/console --run --tonicsCloud --jobQueue` to start working on TonicsCloud job queue
 */
class CloudJobQueueManager implements ConsoleCommand, HandlerInterface, SharedMemoryInterface
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--run",
            "--tonicsCloud",
            "--jobQueue"
        ];
    }

    /**
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $this->successMessage('TonicsCloud JobQueue work mode ON, started with a memory of ' . helper()->formatBytes(memory_get_usage()));
        TonicsCloudActivator::getJobQueue()->runJob();
    }

    public function handleEvent(object $event): void
    {
        if ($event instanceof OnAddConsoleCommand){
            $event->addConsoleCommand($this);
        }

        if ($event instanceof OnStartUpCLI){
            $event->addClass(get_class($this));
        }
    }

    public static function masterKey(): string
    {
        return self::class;
    }

    public static function semaphoreID(): int
    {
        return ftok(__FILE__, 't');
    }

    public static function sharedMemorySize(): string
    {
        return '500kb';
    }
}