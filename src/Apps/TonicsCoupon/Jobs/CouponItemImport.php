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
            $couponData = null;
            db(onGetDB: function ($db) use ($coupon, &$couponData){
                $couponData = $db->Select("coupon_slug, coupon_id")->From(TonicsCouponActivator::couponTableName())
                    ->WhereEquals('coupon_slug', $coupon['coupon_slug'])
                    ->FetchFirst();
            });
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