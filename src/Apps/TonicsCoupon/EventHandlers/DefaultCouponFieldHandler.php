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

use App\Apps\TonicsCoupon\Events\OnCouponDefaultField;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DefaultCouponFieldHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnCouponDefaultField */
        $event->addDefaultField('app-tonicscoupon-coupon-page')->addDefaultField('seo-settings')
            ->addDefaultField('site-header', true)
            ->addDefaultField('site-footer', true);
    }
}