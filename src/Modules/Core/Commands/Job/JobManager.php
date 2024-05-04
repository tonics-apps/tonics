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

namespace App\Modules\Core\Commands\Job;

use App\Modules\Core\Commands\OnStartUpCLI;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SharedMemoryInterface;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

/**
 * RUN: `php bin/console --run --job` to start working on jobs
 */
class JobManager implements ConsoleCommand, HandlerInterface, SharedMemoryInterface
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--run",
            "--job"
        ];
    }

    /**
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $this->successMessage('Job work mode ON, started with a memory of ' . helper()->formatBytes(memory_get_usage()));
        job()->runJob();
    }

    public function handleEvent(object $event): void
    {
        /** @var $event OnStartUpCLI */
        $event->addClass(get_class($this));
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