<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Commands;

use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class SetupTonics implements ConsoleCommand, EventInterface
{

    public function required(): array
    {
        return [
            "--run",
            "--setup",

            "--user",
            "--email",
            "--pass",
        ];
    }

    public function run(array $commandOptions): void
    {
        $user = $commandOptions['--user'];
        $email = $commandOptions['--email'];
        $pass = $commandOptions['--pass'];
        dd($commandOptions, [$user, $email, $pass]);
        // TODO: Implement run() method.
    }

    public function event(): static
    {
        // TODO: Implement event() method.
    }
}