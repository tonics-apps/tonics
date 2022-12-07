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

use App\Apps\TonicsCoupon\Events\OnCouponTypeCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewCouponTypeSlugIDGeneration implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        ## The iteration should only go once, but in a unlikely case that a collision occur, we try force updating the slugID until we max out 10 iterations
        ## but it should never happen even if you have 10Million posts
        $iterations = 10;
        for ($i = 0; $i < $iterations; ++$i) {
            try {
                $this->updateSlugID($event);
                break;
            } catch (\Exception){
                // log.. Collision occur message
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function updateSlugID($event)
    {
        /**
         * @var OnCouponTypeCreate $event
         */
        $slugGen = $event->getCouponTypeID() . random_int(PHP_INT_MIN, PHP_INT_MAX). hrtime(true);
        $slugGen = hash('xxh3', $slugGen);
        $updateChanges = $event->getCouponData()->createCouponType(['coupon_type_slug'], false);
        $updateChanges['slug_id'] = $slugGen;
        db()->FastUpdate($event->getCouponData()->getCouponTypeTable(), $updateChanges, db()->Where('coupon_type_id', '=', $event->getCouponTypeID()));
        $event->getCouponType()->slug_id = $slugGen;
    }
}