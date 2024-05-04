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

    private bool $exitProcess = false;

    /**
     * @throws \Exception
     */
    public function run(callable $onRunning, callable $shutDown = null)
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
        if ($shutDown){
            $shutDown();
        }

        exit(0);
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function registerSignalHandlers(): void
    {
        pcntl_signal(SIGTERM, function () {
            $this->infoMessage("Gracefully Shutting Down, ending with a memory of " . helper()->formatBytes(memory_get_usage()));
            $this->exitProcess = true;
        });
    }

}