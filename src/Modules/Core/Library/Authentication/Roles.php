<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\Authentication;

use App\Modules\Core\Library\Tables;
use JetBrains\PhpStorm\Pure;

final class Roles
{

    #
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
    const CAN_DELETE= 'CAN_DELETE';

    # MODULES...
    const CAN_ACCESS_CORE = 'CAN_ACCESS_CORE';
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

    // Since this is bit-shift, I need to ensure the array index starts at 1
    static array $PERMISSIONS = [
        1 => self::CAN_READ, self::CAN_WRITE, self::CAN_UPDATE, self::CAN_DELETE, self::CAN_ACCESS_CORE, self::CAN_ACCESS_CUSTOMER, self::CAN_ACCESS_MEDIA,
        self::CAN_ACCESS_MENU, self::CAN_ACCESS_PAGE, self::CAN_ACCESS_PAYMENT, self::CAN_ACCESS_POST, self::CAN_ACCESS_TRACK, self::CAN_ACCESS_WIDGET,
        self::CAN_ACCESS_MODULE, self::CAN_ACCESS_APPS, self::CAN_ACCESS_FIELD, self::CAN_UPDATE_MODULES, self::CAN_UPDATE_APPS
    ];

    public static function getPermission(string $permissionName): int
    {
        $roles = array_flip(self::$PERMISSIONS);
        if (!key_exists($permissionName, $roles)){
            throw new \InvalidArgumentException("`$permissionName` is an invalid permission name");
        }

        return $roles[$permissionName];
    }

    # THE ABOVE IS FOR PERMISSION

    # THE BELOW IS FOR ROLE
    const ROLE_READ = 'ROLE_READ';
    const ROLE_WRITE = 'ROLE_WRITE';
    const ROLE_UPDATE = 'ROLE_UPDATE';
    const ROLE_DELETE = 'ROLE_DELETE';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_CUSTOMER = 'ROLE_CUSTOMER';

    static array $ROLES = [
        self::ROLE_READ,
        self::ROLE_WRITE,
        self::ROLE_UPDATE,
        self::ROLE_DELETE,
        self::ROLE_ADMIN,
        self::ROLE_CUSTOMER
    ];

    /**
     * This gets the ID of the role, not that this is not the role bit-shifted number but just the auto-incremented id,
     * this way, you can use it as a foreign key
     * @throws \Exception
     */
    public static function getRoleIDFromDB(string $roleName): ?int
    {
        $roles = array_flip(self::$ROLES);
        if (!key_exists($roleName, $roles)){
            throw new \InvalidArgumentException("`$roleName` is an invalid role name");
        }

        $roleData = db()->Select('id')->From(Tables::getTable(Tables::ROLES))->WhereEquals('role_name', $roleName)->FetchFirst();

        if (isset($roleData->id)){
            return $roleData->id;
        }

        return null;
    }

    public static function ROLE_READ(): string
    {
        $adminPermissions = [
            self::shiftLeft(self::getPermission(self::CAN_READ)),
        ];
        return self::gmpOr($adminPermissions);
    }

    public static function ROLE_WRITE(): string
    {
        $adminPermissions = [
            self::shiftLeft(self::getPermission(self::CAN_WRITE)),
        ];
        return self::gmpOr($adminPermissions);
    }

    public static function ROLE_UPDATE(): string
    {
        $adminPermissions = [
            self::shiftLeft(self::getPermission(self::CAN_UPDATE)),
        ];
        return self::gmpOr($adminPermissions);
    }

    public static function ROLE_DELETE(): string
    {
        $adminPermissions = [
            self::shiftLeft(self::getPermission(self::CAN_DELETE)),
        ];
        return self::gmpOr($adminPermissions);
    }

    public static function ROLE_ADMIN(): string
    {
        $adminPermissions = [
            self::shiftLeft(self::getPermission(self::CAN_READ)),
            self::shiftLeft(self::getPermission(self::CAN_WRITE)),
            self::shiftLeft(self::getPermission(self::CAN_UPDATE)),
            self::shiftLeft(self::getPermission(self::CAN_DELETE)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_CORE)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_MEDIA)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_MENU)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_PAGE)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_PAYMENT)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_POST)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_TRACK)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_WIDGET)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_FIELD)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_APPS)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_MODULE)),
            self::shiftLeft(self::getPermission(self::CAN_UPDATE_MODULES)),
            self::shiftLeft(self::getPermission(self::CAN_UPDATE_APPS)),
        ];
        return self::gmpOr($adminPermissions);
    }

    public static function ROLE_CUSTOMER(): string
    {
        $permissions = [
            self::shiftLeft(self::getPermission(self::CAN_READ)),
            self::shiftLeft(self::getPermission(self::CAN_ACCESS_CUSTOMER)),
        ];
        return self::gmpOr($permissions);
    }


    /**
     * Check if Role have access to permissions
     * @param string|int $role
     * Example of role Roles::ADMIN(), Roles::Customer() or a numeric roleID stored in a database
     * @param int $permission
     * example of permission: CAN_ACCESS_WIDGET
     * @return bool
     */
    #[Pure] public static function RoleHasPermission(string|int $role, int $permission): bool
    {
        if (empty($role)){
            return false;
        }
        $permission = self::shiftLeft($permission);
        $result = gmp_strval(gmp_and($role, $permission));
        return !empty($result);
    }

    /**
     * Return true if role has all the permission in $perms, otherwise, false
     *
     * @param string $role
     * Example of role Roles::ADMIN(), Roles::Customer() or a numeric roleID stored in a database
     * @param array $perms
     * @return bool
     */
    #[Pure] public static function RoleHasMultiplePermission(string $role, array $perms): bool
    {
        $result = true;
        foreach ($perms as $p){
            if (self::RoleHasPermission($role, $p) === false){
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * Arbitrary binary shift left operation
     * @param int $exp
     * @return string
     */
    public static function shiftLeft(int $exp): string
    {
        // This is same as 1 << $exp (except that the below can perform arbitrary shift left)
        return bcmul('1', bcpow(2, $exp));
    }

    /**
     * BITWISE OF MULTIPLE NUMBERS
     * @param array $numbers
     * @return string
     */
    public static function gmpOr(array $numbers): string
    {
        $total = 0;
        foreach($numbers as $num){
            $total = gmp_or($total, $num);
        }
        return gmp_strval($total);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function updateRolesInDatabase(): void
    {
        $rolesToInsert = [];
        foreach (Roles::$ROLES as $roleName){
            if (method_exists(Roles::class, $roleName)){
                $rolesToInsert[] = [
                    'role_id' => Roles::$roleName(),
                    'role_name' => $roleName,
                ];
            }
        }

        db()->insertOnDuplicate(Tables::getTable(Tables::ROLES), $rolesToInsert, ['role_name']);
    }
}