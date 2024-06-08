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
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\States\UpdateMechanismState;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;


/**
 * This is the module, and apps update mechanism, you can store the latest releases, or you could update every one of them.
 *
 * Where:
 *
 * - --update: can take a comma list of items to update, leave it as is to update or discover all
 * - --type: type of update you are doing, e.g. `php bin/console --update --type=module --action=discover`, type can be module, or app
 * if no type is given, it would do nothing.
 * - --action: action would either be to discover latest or update, when set to update, it discovers and update
 *
 * <br>
 * Note: If you pass more than one types i.e. `--type=module,app`, the list of items in --update flag won't be taken into account as it
 * would be ambiguous.
 * <br>
 * However, There is nothing stopping you from running a separate command, e.g:
 * <br>
 * `php bin/console --update=item1,item2,etc --type=module --action=discover`
 * <br>
 * `php bin/console --update=item1,item2,etc --type=app --action=discover`
 * <br>
 * `php bin/console --update=item1,item2,etc --type=app --action=update`
 */
class Updates implements ConsoleCommand
{

    public function required (): array
    {
        return [
            "--update",
            "--type",
            "--action",
        ];
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function run (array $commandOptions): void
    {
        $actions = explode(',', $commandOptions['--action'])[0];
        $updateMechanismState = new UpdateMechanismState([
            UpdateMechanismState::SettingsKeyUpdates => explode(',', $commandOptions['--update']),
            UpdateMechanismState::SettingsKeyTypes   => explode(',', $commandOptions['--type']),
            UpdateMechanismState::SettingsKeyAction  => $actions,
        ]);
        $updateMechanismState->runStates(false);
        if ($updateMechanismState->getStateResult() === SimpleState::DONE) {
            if ($actions === 'update') {
                AppConfig::addUpdateMigrationsJob();
                AppConfig::updateRestartService();
                helper()->sendMsg($updateMechanismState->getStateResult(), "Update SuccessFull", 'close');
                session()->flash(["Update SuccessFull"], [], type: Session::SessionCategories_FlashMessageSuccess);
            }
        } else {
            helper()->sendMsg($updateMechanismState->getStateResult(), "Error Occur Updating", 'close');
            session()->flash(["Error Occur Updating"], []);
        }
    }
}