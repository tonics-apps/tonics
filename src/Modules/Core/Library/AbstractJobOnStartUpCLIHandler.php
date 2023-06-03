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