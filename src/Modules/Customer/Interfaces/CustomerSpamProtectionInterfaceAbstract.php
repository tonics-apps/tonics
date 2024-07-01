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

namespace App\Modules\Customer\Interfaces;

use App\Modules\Customer\Events\OnAddCustomerSpamProtectionEvent;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

abstract class CustomerSpamProtectionInterfaceAbstract implements CustomerSpamProtectionInterface, HandlerInterface
{
    public function handleEvent (object $event): void
    {
        /** @var $event OnAddCustomerSpamProtectionEvent */
        $event->addHandler($this);
    }
}