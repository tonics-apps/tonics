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

namespace App\Apps\TonicsCloud\Events;

use App\Apps\TonicsCloud\Interfaces\CloudAutomationInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddCloudAutomationEvent implements EventInterface
{
    private array $cloudAutomations = [];

    public function event (): static
    {
        return $this;
    }

    public function addCloudAutomationHandler (CloudAutomationInterface $cloudAutomation): static
    {
        $this->cloudAutomations[strtolower($cloudAutomation->name())] = $cloudAutomation;
        return $this;
    }

    public function exist (string $name): bool
    {
        $name = strtolower($name);
        return isset($this->cloudAutomations[$name]);
    }

    /**
     * @throws \Exception
     */
    public function getCloudAutomationHandler (string $name): mixed
    {
        $name = strtolower($name);
        if (isset($this->cloudAutomations[$name])) {
            return $this->cloudAutomations[$name];
        }

        throw new \Exception("$name is an unknown cloud automation handler name");
    }

    /**
     * @return array
     */
    public function getCloudAutomations (): array
    {
        return $this->cloudAutomations;
    }

    /**
     * @param array $cloudAutomations
     */
    public function setCloudAutomations (array $cloudAutomations): void
    {
        $this->cloudAutomations = $cloudAutomations;
    }
}