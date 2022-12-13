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
use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;

class CouponItemImport extends AbstractJobInterface implements JobHandlerInterface
{
    use ConsoleColor;

    private CouponController $couponController;

    public function __construct(CouponController $couponController)
    {
        $this->couponController = $couponController;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function handle(): void
    {
        $coupon = $this->getDataAsArray();
        if (isset($coupon['coupon_slug'])) {
            $this->getCouponController()->setIsUserInCLI(True);
            $_POST = $coupon;
            $couponData = db()->Select("coupon_slug, coupon_id")->From(TonicsCouponActivator::couponTableName())
                ->WhereEquals('coupon_slug', $coupon['coupon_slug'])
                ->FetchFirst();
            if (isset($couponData->coupon_slug)) {
                $_POST['coupon_id'] = $couponData->coupon_id;
                $this->getCouponController()->update($couponData->coupon_slug);
                $this->successMessage($coupon['coupon_name'] . " [Coupon Updated] ");
            } else {
                $this->getCouponController()->store();
                $this->successMessage($coupon['coupon_name'] . " [Coupon Created] ");
            }
        } else {
            throw new \Exception("Failed To Import Coupon Item - Malformed Coupon Data");
        }
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