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

namespace App\Apps\TonicsCloud\EventHandlers;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Events\OnAddRole;
use App\Modules\Core\Library\Authentication\RolePermissionInterface;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsCloudPermissionRole implements HandlerInterface, RolePermissionInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnAddRole */
        $event->addRole($this);
    }

    public function getRoleName (): string
    {
        return Roles::ROLE_CUSTOMER;
    }

    /**
     * @return array
     */
    public function getRolePermissions (): array
    {
        return TonicsCloudActivator::DEFAULT_PERMISSIONS();
    }
}