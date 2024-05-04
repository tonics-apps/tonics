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

namespace App\Apps\TonicsCoupon\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

/**
 * This Listens to the OnAdminMenu and Whenever the event fires, we call this listener
 *
 * The purpose of this listener is to add post menu functionality, such as:
 * showing post menu, adding new post menu, category menu, and anything related to post
 * Class DefaultTemplate
 * @package Modules\Core\EventHandlers
 */
class CouponMenus implements HandlerInterface
{
    const COUPON = AdminMenuHelper::DASHBOARD . '/TONICS_COUPON';
    const COUPON_NEW = self::COUPON . '/NEW_COUPON';
    const COUPON_ALL = self::COUPON . '/ALL_COUPON';
    const COUPON_TYPE_NEW = self::COUPON . '/NEW_COUPON_TYPE';
    const COUPON_TYPE_ALL = self::COUPON . '/ALL_COUPON_TYPE';

    /**
     * @param object $event
     * @throws \Exception|\Throwable
     */
    public function handleEvent(object $event): void
    {
        tree()->group('', function (Tree $tree){

            $tree->add(self::COUPON, [
                'mt_name' => 'Coupon',
                'mt_url_slug' => route('tonicsCoupon.create'),
                'mt_icon' => helper()->getIcon('offer', 'icon:admin')
            ]);

            $tree->add(self::COUPON_NEW, [
                'mt_name' => 'New Coupon',
                'mt_url_slug' => route('tonicsCoupon.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(self::COUPON_ALL, [
                'mt_name' => 'All Coupons',
                'mt_url_slug' => route('tonicsCoupon.index'),
                'mt_icon' => helper()->getIcon('notes', 'icon:admin')
            ]);

            $tree->add(self::COUPON_TYPE_NEW, [
                'mt_name' => 'New Coupon Type',
                'mt_url_slug' => route('tonicsCoupon.Type.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(self::COUPON_TYPE_ALL, [
                'mt_name' => 'New Coupon Type',
                'mt_url_slug' => route('tonicsCoupon.Type.index'),
                'mt_icon' => helper()->getIcon('category', 'icon:admin')
            ]);

        },['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_APPS])]);
    }
}
