<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Customer\Events;

use App\Modules\Customer\Interfaces\CustomerSpamProtectionInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddCustomerSpamProtectionEvent implements EventInterface
{
    private array $customerSpamProtections = [];

    public function event (): static
    {
        return $this;
    }

    public function addHandler (CustomerSpamProtectionInterface $customerSpamProtection): static
    {
        $this->customerSpamProtections[strtolower($customerSpamProtection->name())] = $customerSpamProtection;
        return $this;
    }

    public function exist (string $name): bool
    {
        $name = strtolower($name);
        return isset($this->customerSpamProtections[$name]);
    }

    /**
     * @return array|CustomerSpamProtectionInterface[]
     */
    public function getCustomerSpamProtections (): array
    {
        return $this->customerSpamProtections;
    }

}