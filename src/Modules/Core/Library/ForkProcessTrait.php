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