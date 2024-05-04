<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Library\Authentication;

use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\Pure;

final class Roles
{

    private static $permissions = null;

    #
    # NOTE: THE BELOW WARNING NO LONGER APPLIES AS I HAVE SWITCHED FROM MANAGING THE ROLES AND PERMISSION FROM BITS SHIFT TO USING DATABASE TABLES

    # DON'T FALL FOR A TRAP: If you want to add extra permission, add it below the last CAN_,
    # e.g. if you add CAN_NEW = 2 between CAN_READ = 1 and CAN_WRITE = 2, and you gave CAN_NEW number 2,
    # while updating the rest of the number incrementally (meaning CAN_WRITE would now start from 3, and you bump the rest by one),
    # the problem you'll face is the permission of CAN_WRITE would be transfer to CAN_NEW (you don't want to mess up permissions).
    #
    # To be on a safer side, add it below the last can, e.g. if the las CAN_ is given number 20, give the new CAN_ number 21 and so on.
    #

    const CAN_READ = 'CAN_READ';
    const CAN_WRITE = 'CAN_WRITE';
    const CAN_UPDATE = 'CAN_UPDATE';
    const CAN_DELETE = 'CAN_DELETE';

    # MODULES...
    const CAN_ACCESS_CORE = 'CAN_ACCESS_CORE';
    const CAN_ACCESS_GUEST = 'CAN_ACCESS_GUEST'; // For Guest User, Not a Module Per se
    const CAN_ACCESS_CUSTOMER = 'CAN_ACCESS_CUSTOMER';
    const CAN_ACCESS_MEDIA = 'CAN_ACCESS_MEDIA';
    const CAN_ACCESS_MENU = 'CAN_ACCESS_MENU';
    const CAN_ACCESS_PAGE = 'CAN_ACCESS_PAGE';
    const CAN_ACCESS_PAYMENT = 'CAN_ACCESS_PAYMENT';
    const CAN_ACCESS_POST = 'CAN_ACCESS_POST';
    const CAN_ACCESS_TRACK = 'CAN_ACCESS_TRACK';
    const CAN_ACCESS_WIDGET = 'CAN_ACCESS_WIDGET';

    # MODULE
    const CAN_ACCESS_MODULE = 'CAN_ACCESS_MODULE';

    # THEMES and PLUGIN...
    const CAN_ACCESS_APPS = 'CAN_ACCESS_APPS';

    # FIELD
    const CAN_ACCESS_FIELD = 'CAN_ACCESS_FIELD';

    # Update Mechanism
    const CAN_UPDATE_MODULES = 'CAN_UPDATE_MODULES';
    const CAN_UPDATE_APPS = 'CAN_UPDATE_APPS';


    # THE ABOVE IS FOR PERMISSION

    # THE BELOW IS FOR ROLE
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_GUEST = 'ROLE_GUEST';
    const ROLE_CUSTOMER = 'ROLE_CUSTOMER';

    static array $ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_GUEST,
        self::ROLE_CUSTOMER
    ];

    /**
     * This gets the ID of the role, note that this is not the role bit-shifted number but just the auto-incremented id,
     * this way, you can use it as a foreign key
     * @throws \Exception
     */
    public static function getRoleIDFromDB(string $roleName): ?int
    {
        $roles = array_flip(self::$ROLES);
        if (!key_exists($roleName, $roles)) {
            throw new \InvalidArgumentException("`$roleName` is an invalid role name");
        }

        $roleData = null;
        db(onGetDB: function ($db) use ($roleName, &$roleData) {
            $roleData = $db->Select('role_id')->From(Tables::getTable(Tables::ROLES))
                ->WhereEquals('role_name', $roleName)
                ->FetchFirst();
        });

        if (isset($roleData->role_id)) {
            return $roleData->role_id;
        }

        return null;
    }

    public static function ROLE_ADMIN(): array
    {
        return [
            self::CAN_READ,
            self::CAN_WRITE,
            self::CAN_UPDATE,
            self::CAN_DELETE,
            self::CAN_ACCESS_CORE,
            self::CAN_ACCESS_MEDIA,
            self::CAN_ACCESS_MENU,
            self::CAN_ACCESS_PAGE,
            self::CAN_ACCESS_PAYMENT,
            self::CAN_ACCESS_POST,
            self::CAN_ACCESS_TRACK,
            self::CAN_ACCESS_WIDGET,
            self::CAN_ACCESS_FIELD,
            self::CAN_ACCESS_APPS,
            self::CAN_ACCESS_MODULE,
            self::CAN_UPDATE_MODULES,
            self::CAN_UPDATE_APPS,
        ];
    }

    public static function ROLE_GUEST(): array
    {
        return [
            self::CAN_READ,
            self::CAN_ACCESS_GUEST,
        ];
    }

    public static function ROLE_CUSTOMER(): array
    {
        return [
            self::CAN_READ,
            self::CAN_ACCESS_CUSTOMER,
        ];
    }

    /**
     * Returns true if roles has permission or permissions
     *
     * @param string|int $role
     * If string, then an example is: Roles::ROLE_ADMIN
     * @param array|string $permissions
     * Can be an array or just a string, e.g: CAN_READ, or for array: ['CAN_READ', 'CAN_WRITE', ...]
     * @return bool
     * @throws \Exception
     */
    public static function ROLE_HAS_PERMISSIONS(string|int $role, array|string $permissions): bool
    {
        if (empty($role)) {
            return false;
        }

        if (is_string($permissions)){
            $permissions = [$permissions];
        }

        $result = false;
        db( onGetDB: function (TonicsQuery $db) use ($permissions, &$role, &$result) {
            $rpTable = Tables::getTable(Tables::ROLE_PERMISSIONS);
            $pTable = Tables::getTable(Tables::PERMISSIONS);
            $rTable = Tables::getTable(Tables::ROLES);

            if (is_string($role)){
                $role = $db->Q()->Select('role_id')
                    ->From($rTable)
                    ->WhereEquals('role_name', $role)->FetchFirst()?->role_id;
            }

            $hasPermission = $db->Q()->Select()->Count()->As('count_perm')->From("$rTable r")
                ->Join("$rpTable rp", "r.role_id", "rp.fk_role_id")
                ->Join("$pTable p", "rp.fk_permission_id", "p.permission_id")
                ->WhereEquals("r.role_id", $role)->WhereIn("p.permission_name", $permissions)->FetchFirst();

            if(isset($hasPermission->count_perm)){
                $result = $hasPermission->count_perm === count($permissions);
            }
        });

        return $result;
    }

    /**
     * Deprecated
     * It is important, you re-login or invalidate existing session roles after calling this function,
     * otherwise, the old session roles would be used.
     * @return void
     * @throws \Exception
     */
    public static function updateRolesInDatabase(): void
    {
        $rolesToInsert = [];
        foreach (Roles::$ROLES as $roleName) {
            if (method_exists(Roles::class, $roleName)) {
                $rolesToInsert[] = [
                    'role_id' => Roles::$roleName(),
                    'role_name' => $roleName,
                ];
            }
        }

        db(onGetDB: function ($db) use ($rolesToInsert) {
            $db->insertOnDuplicate(Tables::getTable(Tables::ROLES), $rolesToInsert, ['role_id']);
        });
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function UPDATE_DEFAULT_ROLES(): void
    {
        $rolesToInsert = [];
        foreach (self::DEFAULT_ROLES() as $ROLE) {
            $rolesToInsert[] = [
                'role_name' => $ROLE,
            ];
        }

        db(onGetDB: function (TonicsQuery $db) use ($rolesToInsert) {
            $db->insertOnDuplicate(Tables::getTable(Tables::ROLES), $rolesToInsert, ['role_name']);
        });
    }

    /**
     * @param array $defaultPermissions
     * @return void
     * @throws \Exception
     */
    public static function UPDATE_DEFAULT_PERMISSIONS(array $defaultPermissions = []): void
    {
        if (empty($defaultPermissions)){
            $defaultPermissions = self::DEFAULT_PERMISSIONS();
        }

        $rolesToInsert = [];
        foreach ($defaultPermissions as $PERMISSION) {
            $rolesToInsert[] = [
                'permission_display_name' => $PERMISSION,
                'permission_name' => $PERMISSION,
            ];
        }

        db(onGetDB: function (TonicsQuery $db) use ($rolesToInsert) {
            $db->insertOnDuplicate(Tables::getTable(Tables::PERMISSIONS), $rolesToInsert, ['permission_name']);
        });
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function UPDATE_DEFAULT_ROLES_PERMISSIONS(): void
    {
        db( onGetDB: function (TonicsQuery $db){
            $rolesToInsert = [];
            $table = Tables::getTable(Tables::ROLE_PERMISSIONS);
            foreach (self::DEFAULT_ROLES() as $ROLE){
                if (method_exists(Roles::class, $ROLE)) {
                    $roleID = $db->Q()->Select('role_id')
                        ->From(Tables::getTable(Tables::ROLES))
                        ->WhereEquals('role_name', $ROLE)->FetchFirst()?->role_id;

                    $permissions = $db->Q()->Select('permission_id')
                        ->From(Tables::getTable(Tables::PERMISSIONS))
                        ->WhereIn('permission_name', Roles::$ROLE())->FetchResult();

                    if (!empty($roleID) && (is_array($permissions) && !empty($permissions))){
                        $db->Q()->FastDelete($table, db()->WhereIn('fk_role_id', $roleID));
                        foreach ($permissions as $permission) {
                            $rolesToInsert[] = [
                                'fk_role_id' => $roleID,
                                'fk_permission_id' => $permission->permission_id,
                            ];
                        }
                    }
                }
            }
            $db->Q()->Insert($table, $rolesToInsert);
        });
    }

    public static function DEFAULT_ROLES(): array
    {
        return self::$ROLES;
    }

    public static function DEFAULT_PERMISSIONS(): array
    {
        return [
            self::CAN_READ, self::CAN_WRITE, self::CAN_UPDATE, self::CAN_DELETE, self::CAN_ACCESS_CORE, self::CAN_ACCESS_GUEST, self::CAN_ACCESS_CUSTOMER, self::CAN_ACCESS_MEDIA,
            self::CAN_ACCESS_MENU, self::CAN_ACCESS_PAGE, self::CAN_ACCESS_PAYMENT, self::CAN_ACCESS_POST, self::CAN_ACCESS_TRACK, self::CAN_ACCESS_WIDGET,
            self::CAN_ACCESS_MODULE, self::CAN_ACCESS_APPS, self::CAN_ACCESS_FIELD, self::CAN_UPDATE_MODULES, self::CAN_UPDATE_APPS
        ];
    }

    /**
     * Returns permissions ID
     * @param array $permissions
     * An Array of Permissions you want its IDS: ['CAN_READ', 'CAN_WRITE', ...]
     * @return array
     * @throws \Exception
     */
    public static function GET_PERMISSIONS_ID(array $permissions): array
    {
        $perm = [];
        $collatedPermissions = self::GET_ALL_PERMISSIONS();
        foreach ($permissions as $permission){
            if (isset($collatedPermissions[$permission])){
                $perm[] = $collatedPermissions[$permission];
            }
        }

        return $perm;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public static function GET_ALL_PERMISSIONS(): mixed
    {
        if (!self::$permissions) {
            $permission = null;
            db(onGetDB: function (TonicsQuery $db) use (&$permission){
                $table = Tables::getTable(Tables::PERMISSIONS);
                $permission = $db->Select('permission_name, permission_id')
                    ->From($table)->setPdoFetchType(\PDO::FETCH_KEY_PAIR)
                    ->FetchResult();
            });

            self::$permissions = $permission;
        }

        return self::$permissions;
    }
}