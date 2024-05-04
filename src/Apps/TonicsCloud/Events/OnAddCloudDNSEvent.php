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

use App\Apps\TonicsCloud\Interfaces\CloudDNSInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddCloudDNSEvent implements EventInterface
{
    private array $cloudDNS = [];

    public function event(): static
    {
        return $this;
    }

    public function addCloudServerHandler(CloudDNSInterface $cloudServer): static
    {
        $this->cloudDNS[strtolower($cloudServer->name())] = $cloudServer;
        return $this;
    }

    public function exist(string $name): bool
    {
        $name = strtolower($name);
        return isset($this->cloudDNS[$name]);
    }

    /**
     * @throws \Exception
     */
    public function getCloudDNSHandler(string $name): mixed
    {
        $name = strtolower($name);
        if (isset($this->cloudDNS[$name])){
            return $this->cloudDNS[$name];
        }

        throw new \Exception("$name is an unknown payment handler name");
    }

    /**
     * @return array
     */
    public function getCloudDNS(): array
    {
        return $this->cloudDNS;
    }

    /**
     * @param array $cloudDNS
     */
    public function setCloudDNS(array $cloudDNS): void
    {
        $this->cloudDNS = $cloudDNS;
    }
}