<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Events;

use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddConsoleCommand implements EventInterface
{
    private array $consoleCommands = [];

    public function event(): static
    {
        return $this;
    }

    public function addConsoleCommand(ConsoleCommand $consoleCommand): static
    {
        $this->consoleCommands[strtolower($consoleCommand::class)] = $consoleCommand::class;
        return $this;
    }

    /**
     * @return array
     */
    public function getConsoleCommands(): array
    {
        return $this->consoleCommands;
    }

    public function exist(string $name): bool
    {
        $name = strtolower($name);
        return isset($this->consoleCommands[$name]);
    }
}