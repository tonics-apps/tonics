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