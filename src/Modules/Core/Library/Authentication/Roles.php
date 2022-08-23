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

    const CAN_READ = 1;
    const CAN_WRITE = 2;
    const CAN_UPDATE = 3;
    const CAN_DELETE= 4;

    # MODULES...
    const CAN_ACCESS_CORE = 5;
    const CAN_ACCESS_CUSTOMER = 6;
    const CAN_ACCESS_MEDIA = 7;
    const CAN_ACCESS_MENU = 8;
    const CAN_ACCESS_PAGE = 9;
    const CAN_ACCESS_PAYMENT = 10;
    const CAN_ACCESS_POST = 11;
    const CAN_ACCESS_TRACK = 12;
    const CAN_ACCESS_WIDGET = 13;

    # MODULE
    const CAN_ACCESS_MODULE = 14;

    # THEMES and PLUGIN...
    const CAN_ACCESS_APPS = 15;

    # FIELD
    const CAN_ACCESS_FIELD = 16;

    # Update Mechanism
    const CAN_UPDATE_MODULES = 17;
    const CAN_UPDATE_APPS = 18;

    #[Pure] public static function ADMIN(): string
    {
        $adminPermissions = [
            self::shiftLeft(self::CAN_READ),
            self::shiftLeft(self::CAN_WRITE),
            self::shiftLeft(self::CAN_UPDATE),
            self::shiftLeft(self::CAN_DELETE),
            self::shiftLeft(self::CAN_ACCESS_CORE),
            self::shiftLeft(self::CAN_ACCESS_MEDIA),
            self::shiftLeft(self::CAN_ACCESS_MENU),
            self::shiftLeft(self::CAN_ACCESS_PAGE),
            self::shiftLeft(self::CAN_ACCESS_PAYMENT),
            self::shiftLeft(self::CAN_ACCESS_POST),
            self::shiftLeft(self::CAN_ACCESS_TRACK),
            self::shiftLeft(self::CAN_ACCESS_WIDGET),
            self::shiftLeft(self::CAN_ACCESS_FIELD),
            self::shiftLeft(self::CAN_ACCESS_APPS),
            self::shiftLeft(self::CAN_ACCESS_MODULE),

            self::shiftLeft(self::CAN_UPDATE_MODULES),
            self::shiftLeft(self::CAN_UPDATE_APPS),
        ];
        return self::gmpOr($adminPermissions);
    }

    public static function UPDATE_MAINTAINER(): string
    {
        $permissions = [
            self::shiftLeft(self::CAN_UPDATE_MODULES),
            self::shiftLeft(self::CAN_UPDATE_APPS),
        ];
        return self::gmpOr($permissions);
    }

    #[Pure] public static function POST_WRITER(): string
    {
        $permissions = [
            self::shiftLeft(self::CAN_READ),
            self::shiftLeft(self::CAN_WRITE),
            self::shiftLeft(self::CAN_UPDATE),
            self::shiftLeft(self::CAN_ACCESS_CORE),
            self::shiftLeft(self::CAN_ACCESS_MEDIA),
            self::shiftLeft(self::CAN_ACCESS_POST),
        ];
        return self::gmpOr($permissions);
    }

    #[Pure] public static function TRACK_WRITER(): string
    {
        $permissions = [
            self::shiftLeft(self::CAN_READ),
            self::shiftLeft(self::CAN_WRITE),
            self::shiftLeft(self::CAN_UPDATE),
            self::shiftLeft(self::CAN_ACCESS_CORE),
            self::shiftLeft(self::CAN_ACCESS_MEDIA),
            self::shiftLeft(self::CAN_ACCESS_TRACK),
        ];
        return self::gmpOr($permissions);
    }

    #[Pure] public static function CUSTOMER(): string
    {
        $permissions = [
            self::shiftLeft(self::CAN_READ),
            self::shiftLeft(self::CAN_ACCESS_CUSTOMER),
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
}