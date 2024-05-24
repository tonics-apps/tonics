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

use App\Modules\Core\Library\JobSystem\JobTransporterInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddCloudJobQueueTransporter implements EventInterface
{

    private array $transporters = [];

    public function event (): static
    {
        return $this;
    }

    public function addJobTransporter (JobTransporterInterface $transporter): static
    {
        $this->transporters[strtolower($transporter->name())] = $transporter;
        return $this;
    }

    /**
     * @return array
     */
    public function getTransporters (): array
    {
        return $this->transporters;
    }

    /**
     * @param array $transporters
     */
    public function setTransporters (array $transporters): void
    {
        $this->transporters = $transporters;
    }

    public function exist (string $name): bool
    {
        $name = strtolower($name);
        return isset($this->transporters[$name]);
    }

    /**
     * @throws \Exception
     */
    public function getTransporter (string $name): mixed
    {
        $name = strtolower($name);
        if (isset($this->transporters[$name])) {
            return $this->transporters[$name];
        }

        throw new \Exception("$name is an unknown cloud job transporter name");
    }
}