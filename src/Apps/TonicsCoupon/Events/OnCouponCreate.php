<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Events;

use App\Apps\TonicsCoupon\Data\CouponData;
use App\Apps\TonicsCoupon\Events\AbstractClasses\CouponDataAccessor;
use App\Modules\Post\Data\PostData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnCouponCreate extends CouponDataAccessor implements EventInterface
{

}