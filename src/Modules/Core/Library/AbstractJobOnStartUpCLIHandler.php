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

use App\Modules\Core\Configs\AppConfig;

class AbstractJobOnStartUpCLIHandler
{
    use ConsoleColor;

    protected array $pIDS        = [];
    protected int   $maxForks    = 10;
    private bool    $exitProcess = false;

    /**
     * @throws \Exception
     */
    public function run (callable $onRunning, callable $shutDown = null)
    {
        $this->registerSignalHandlers();
        while (!$this->exitProcess) {

            if (AppConfig::isMaintenanceMode()) {
                $this->infoMessage("Site in Maintenance Mode...Sleeping");
                usleep(5000000); # Sleep for 5 seconds
                continue;
            }

            $onRunning();
        }

        # Don't take forever here please, this callable give you the chance to
        # do clean up of variables opened outside the run method
        if ($shutDown) {
            $shutDown();
        }

        exit(0);
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function registerSignalHandlers (): void
    {
        pcntl_signal(SIGTERM, function () {
            $this->infoMessage("Gracefully Shutting Down, ending with a memory of " . helper()->formatBytes(memory_get_usage()));
            $this->exitProcess = true;
        });
    }

    /**
     * @param $pid
     *
     * @return void
     * @throws \Exception
     */
    public function collectPIDSInParent ($pid): void
    {
        $this->pIDS[] = $pid; # store the child pid
        // here is where we limit the number of forked process,
        // if the maxed forked has been reached, we wait for any child fork to exit,
        // once it does, we remove the pid that exited from the list (queue) so another one can come in.
        // this effectively limit too many processes from forking
        if (count($this->pIDS) >= $this->maxForks) {
            $pid = pcntl_waitpid(-1, $status);
            unset($this->pIDS[$pid]); // Remove PID that exited from the list
            $this->infoMessage("Maximum Number of {$this->maxForks} JobQueue Forks Reached, Opening For New Fork");

            $pIDSCountBeforeGC = count($this->pIDS);
            $this->infoMessage("Garbage Collecting pIDS To See if We Can Have More Forks At a Go, pIDS Before GC Count is: $pIDSCountBeforeGC");

            $this->garbageCollectPID();

            $pIDSCountAfterGC = count($this->pIDS);
            $this->infoMessage("pIDS After GC Count is: $pIDSCountAfterGC");
        }
    }

    /**
     * Clean PIDS that are no longer running in $this->pIDS, this way, we can run more processes as long as we haven't reached the maxForks limit
     * @return void
     */
    public function garbageCollectPID (): void
    {
        foreach ($this->pIDS as $key => $pID) {
            if (posix_getpgid($pID) === false) {
                unset($this->pIDS[$key]);
            }
        }
    }


}