<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library;

trait ForkProcessTrait
{
    /**
     * - The first line calls the `pcntl_async_signals()` function with TRUE as the argument. This enables asynchronous signal handling.
     *
     * - The next block of code registers a signal handler for the SIGCHLD signal. This signal is sent to the parent process when a child process terminates.
     * The signal handler waits for child processes to complete and kills them with the SIGKILL signal. Without this, zombie process would build as children process runs, so,
     * by killing then when they are done, we avoid that scenario.
     * @return void
     */
    public function cleanHandleZombieProcess(): void
    {
        pcntl_async_signals(true); // May be inherited.

        // Register signal handler for SIGCHLD
        pcntl_signal(SIGCHLD, function ($sigNo) {
            while (($pid = pcntl_waitpid(-1, $status, WNOHANG | WUNTRACED)) > 0) {
                // Child process completed
                posix_kill($pid, SIGKILL); // Kill the child process
            }
        });
    }
}