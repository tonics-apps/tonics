<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands\UpdateMechanism;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\States\UpdateMechanismState;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

/**
 * To run auto-update, you do:
 *
 * <br>
 * `php bin/console --auto-update`
 *
 * This would check the env file to see if all items should be auto updated or some items, this works for modules and apps
 */
class AutoUpdate implements ConsoleCommand
{
    use ConsoleColor;

    public function required(): array
    {
        return [
            "--auto-update",
        ];
    }

    /**
     * @throws \Exception
     */
    public function run(array $commandOptions): void
    {
        $autoUpdateModules = AppConfig::getAutoUpdateModules();
        $autoUpdateApps = AppConfig::getAutoUpdateApps();

        $updateMechanismState = new UpdateMechanismState();

        if ($autoUpdateModules === true || is_array($autoUpdateModules)) {
            $this->infoMessage("Modules Update Initializing...");
            $autoUpdateModules = ($autoUpdateModules === true) ? [] : $autoUpdateModules;
            $updateMechanismState->reset()->setUpdates($autoUpdateModules)->setTypes(['module'])->setAction('update')
                ->runStates(false);
        }


        if ($autoUpdateApps === true || is_array($autoUpdateApps)) {
            $this->infoMessage("Apps Update Initializing...");
            $autoUpdateApps = ($autoUpdateApps === true) ? [] : $autoUpdateApps;
            $updateMechanismState->reset()->setUpdates($autoUpdateApps)->setTypes(['app'])->setAction('update')
                ->runStates(false);
        }

    }
}