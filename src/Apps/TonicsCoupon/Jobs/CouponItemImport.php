<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Jobs;

use App\Apps\TonicsCoupon\Controllers\CouponController;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CouponItemImport extends AbstractJobInterface implements JobHandlerInterface
{
    private CouponController $couponController;

    public function __construct(CouponController $couponController)
    {
        $this->couponController = $couponController;
    }

    public function handle(): void
    {
        dd($this);
        dd($this->getData());
    }

    /**
     * @return CouponController
     */
    public function getCouponController(): CouponController
    {
        return $this->couponController;
    }

    /**
     * @param CouponController $couponController
     */
    public function setCouponController(CouponController $couponController): void
    {
        $this->couponController = $couponController;
    }
}