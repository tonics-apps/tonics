<?php

namespace App\Modules\Core\Commands\UpdateMechanism;

use App\Modules\Core\States\UpdateMechanismState;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;


/**
 * This is the module, plugin and themes update mechanism, you can store the latest releases, or you could update every one of them.
 *
 * Where:
 *
 * - --update: can take a comma list of items to update, leave it as is to update or discover all
 * - --type: type of update you are doing, e.g. `php bin/console --update --type=module --action=discover`, type can be module, plugin, theme or full
 * if no type is given, it uses all types.
 * - --action: action would either be to discover latest or update, when set to update, it discovers and update
 *
 * <br>
 * Note: If you pass more than one types i.e. `--type=module,plugin`, the list of items in --update flag won't be taken into account as it
 * would be ambiguous.
 * <br>
 * However, There is nothing stopping you from running a separate command, e.g:
 * <br>
 * `php bin/console --update=item1,item2,etc --type=module --action=discover`
 * <br>
 * `php bin/console --update=item1,item2,etc --type=plugin --action=update`
 * <br>
 * `php bin/console --update=item1,item2,etc --type=themes --action=update`
 */
class Updates implements ConsoleCommand
{

    public function required(): array
    {
        return [
            "--update",
            "--type",
            "--action"
        ];
    }

    public function run(array $commandOptions): void
    {
        $updates = explode(',', $commandOptions['--update']);
        $types = explode(',', $commandOptions['--type']);
        $actions = explode(',', $commandOptions['--action'])[0];
        $updateMechanismState = new UpdateMechanismState($updates, $types, $actions);
        $updateMechanismState->setCurrentState(UpdateMechanismState::InitialState);
        dd($updates, $types, $actions, $updateMechanismState->runStates());
    }
}