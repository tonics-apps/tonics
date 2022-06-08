<?php

namespace App\Commands\UpdateMechanism;

use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;


/**
 * This is the module, plugin and themes update mechanism, you can store the latest releases, or you could update every one of them.
 *
 * Where:
 *
 * - --update: can take a comma list of items to update, leave it as is to update or discover all
 * - --type: type of update you are doing, e.g. `php bin/console --update --type=module --action=discover`, type can be module, plugin or theme,
 * if no type is given, it uses all types.
 * - --action: action would either be to discover latest or update, when set to update, it discovers and update
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
        // TODO: Implement run() method.
    }
}