<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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