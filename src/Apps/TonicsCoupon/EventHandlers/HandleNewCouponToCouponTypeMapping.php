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

use App\Apps\TonicsCoupon\Events\OnCouponCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewCouponToCouponTypeMapping implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {

        /**
         * @var OnCouponCreate $event
         */
        $toInsert = [];
        foreach ($event->getCouponTypeIDS() as $couponTypeID){
            $toInsert[] = [
                'fk_coupon_type_id' => $couponTypeID,
                'fk_coupon_id' => $event->getCouponID(),
            ];
        }

        db(onGetDB: function ($db) use ($toInsert, $event) {
            $table = $event->getCouponData()->getCouponToTypeTable();
            $db->Insert($table, $toInsert);
        });
    }
}