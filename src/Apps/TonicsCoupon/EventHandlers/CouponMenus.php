<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\EventHandlers;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

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

    /**
     * @param object $event
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_APPS, $event->userRole()), function ($event) {
            $lastMenuID = $event->getLastMenuID() + 5;
            return $event->addMenu($lastMenuID, 'Coupon', helper()->getIcon('cart', 'icon:admin'), route('tonicsCoupon.create'))
                ->addMenu($lastMenuID + 1, 'New Coupon', helper()->getIcon('plus', 'icon:admin'), route('tonicsCoupon.create'), parent: $lastMenuID)
                ->addMenu($lastMenuID + 2, 'All Coupons', helper()->getIcon('notes', 'icon:admin'), route('tonicsCoupon.index'), parent: $lastMenuID)
                ->addMenu($lastMenuID + 3, 'New Coupon Type', helper()->getIcon('plus', 'icon:admin'), route('tonicsCoupon.Type.create'), parent: $lastMenuID)
                ->addMenu($lastMenuID + 4, 'All Coupon Types', helper()->getIcon('category', 'icon:admin'), route('tonicsCoupon.Type.index'), parent: $lastMenuID);
        });
    }
}
