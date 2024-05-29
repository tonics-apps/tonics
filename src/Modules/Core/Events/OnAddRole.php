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

namespace App\Modules\Core\Events;

use App\Modules\Core\Library\Authentication\RolePermissionInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddRole implements EventInterface
{

    private array $roleToPermissions = [];

    public function event (): static
    {
        return $this;
    }

    /**
     * @param RolePermissionInterface $rolePermission
     *
     * @return $this
     */
    public function addRole (RolePermissionInterface $rolePermission): OnAddRole
    {
        $roleName = $this->toUpper($rolePermission->getRoleName());
        if ($this->exist($roleName)) {
            $this->roleToPermissions[$roleName] = [
                ...$this->roleToPermissions[$roleName],
                ...$this->toUpper($rolePermission->getRolePermissions()),
            ];
        } else {
            $this->roleToPermissions[$this->toUpper($rolePermission->getRoleName())] = $this->toUpper($rolePermission->getRolePermissions());
        }

        return $this;
    }

    /**
     * Return all the roles, in the form of:
     *
     * ```
     * ['ROLE_1', 'ROLE_2',...]
     * ```
     * @return int[]|string[]
     */
    public function getRoles (): array
    {
        return array_keys($this->roleToPermissions);
    }

    /**
     *  Return all the permissions, in the form of:
     *
     *  ```
     *  ['PERM_1', 'PERM_2',...]
     *  ```
     * @return array
     */
    public function getPermissions (): array
    {
        $flatten = function ($array) use (&$flatten) {
            $result = [];
            foreach ($array as $value) {
                $result = array_merge($result, is_array($value) ? $flatten($value) : [$value]);
            }
            return $result;
        };
        
        return $flatten($this->getRoleToPermissions());
    }

    /**
     * It would return it in the following form:
     *
     *  ```
     *  [
     *      'ROLE_NAME' => ['PERM_ONE', 'PERM_TWO']
     *  ]
     *  ```
     * @return array
     */
    public function getRoleToPermissions (): array
    {
        return $this->roleToPermissions;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function exist (string $name): bool
    {
        $name = $this->toUpper($name);
        return isset($this->roleToPermissions[$name]);
    }

    /**
     * @param array|string $value
     *
     * @return array|string
     */
    private function toUpper (array|string $value): array|string
    {
        if (is_string($value)) {
            return strtoupper($value);
        }

        return array_map('strtoupper', $value);
    }
}