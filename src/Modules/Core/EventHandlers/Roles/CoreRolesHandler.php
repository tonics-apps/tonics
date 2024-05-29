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

namespace App\Modules\Core\EventHandlers\Roles;

use App\Modules\Core\Events\OnAddRole;
use App\Modules\Core\Library\Authentication\RolePermissionInterface;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CoreRolesHandler implements HandlerInterface
{

    /**
     * @param object $event
     *
     * @return void
     */
    public function handleEvent (object $event): void
    {
        /** @var $event OnAddRole */
        $event
            ->addRole($this->adminRole())
            ->addRole($this->customerRole())
            ->addRole($this->guestRole());
    }

    /**
     * @return RolePermissionInterface
     */
    private function adminRole (): RolePermissionInterface
    {
        return new class implements RolePermissionInterface {
            public function getRoleName (): string
            {
                return Roles::ROLE_ADMIN;
            }

            public function getRolePermissions (): array
            {
                return Roles::ROLE_ADMIN();
            }
        };
    }

    /**
     * @return RolePermissionInterface
     */
    private function customerRole (): RolePermissionInterface
    {
        return new class implements RolePermissionInterface {
            public function getRoleName (): string
            {
                return Roles::ROLE_CUSTOMER;
            }

            public function getRolePermissions (): array
            {
                return Roles::ROLE_CUSTOMER();
            }
        };
    }

    /**
     * @return RolePermissionInterface
     */
    private function guestRole (): RolePermissionInterface
    {
        return new class implements RolePermissionInterface {
            public function getRoleName (): string
            {
                return Roles::ROLE_GUEST;
            }

            public function getRolePermissions (): array
            {
                return Roles::ROLE_GUEST();
            }
        };
    }

}