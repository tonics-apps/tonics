<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands\Job;

use App\Modules\Core\Commands\OnStartUpCLI;
use App\Modules\Core\Library\ConsoleColor;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

/**
 * RUN: `php bin/console --run --job` to start working on jobs
 */
class JobManager implements ConsoleCommand, HandlerInterface
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
}