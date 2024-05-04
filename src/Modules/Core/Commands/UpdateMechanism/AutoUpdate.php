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

        helper()->updateActivateEventStreamMessage(1);

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

        if ($autoUpdateApps || $autoUpdateModules){
            AppConfig::updateRestartService();
        }

    }
}