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

namespace App\Apps\TonicsCoupon\Events\AbstractClasses;

use App\Apps\TonicsCoupon\Data\CouponData;

abstract class CouponDataAccessor
{
    private \stdClass $coupon;
    private CouponData $couponData;

    public function __construct(\stdClass $coupon, CouponData $couponData = null)
    {
        $this->coupon = $coupon;
        if (isset($coupon->created_at)){
            $this->coupon->created_at = $this->getCouponTypeCreatedAt();
        }
        if (isset($coupon->updated_at)){
            $this->coupon->updated_at = $this->getCouponTypeUpdatedAt();
        }
        if (isset($coupon->expired_at)){
            $this->coupon->expired_at = $this->getCouponTypeExpiredAt();
        }
        if ($couponData){
            $this->couponData = $couponData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->coupon;
    }

    public function getAllToArray(): array
    {
        return (array)$this->coupon;
    }

    public function getCouponID(): string|int
    {
        return (property_exists($this->coupon, 'coupon_id')) ? $this->coupon->coupon_id : '';
    }

    public function getFieldIDs(): string|array
    {
        return (property_exists($this->coupon, 'field_ids')) ? json_decode($this->coupon->field_ids) : '';
    }

    public function getSlugID(): mixed
    {
        return (property_exists($this->coupon, 'slug_id')) ? $this->coupon->slug_id : '';
    }

    public function getCouponUserID(): string|int
    {
        return (property_exists($this->coupon, 'user_id')) ? $this->coupon->user_id : '';
    }

    public function getCouponTitle(): string
    {
        return (property_exists($this->coupon, 'coupon_name')) ? $this->coupon->coupon_name : '';
    }

    public function getCouponSlug(): string
    {
        return (property_exists($this->coupon, 'coupon_slug')) ? $this->coupon->coupon_slug : '';
    }

    public function getImageURL(): string
    {
        return (property_exists($this->coupon, 'image_url')) ? $this->coupon->image_url : '';
    }

    public function getCouponStatus(): string|int
    {
        return (property_exists($this->coupon, 'coupon_status')) ? $this->coupon->coupon_status : '';
    }

    public function getCouponTypeIDS(): array
    {
        $catIDS = (property_exists($this->coupon, 'fk_coupon_type_id')) ? $this->coupon->fk_coupon_type_id : [];
        if (!is_array($catIDS) && !empty($catIDS)){
            $catIDS = [$catIDS];
        }
        return $catIDS;
    }

    public function getCouponTypeCreatedAt(): string
    {
        return (property_exists($this->coupon, 'created_at')) ? str_replace(' ', 'T', $this->coupon->created_at) : '';
    }

    public function getCouponTypeUpdatedAt(): string
    {
        return (property_exists($this->coupon, 'updated_at')) ? str_replace(' ', 'T', $this->coupon->updated_at) : '';
    }

    public function getCouponTypeExpiredAt(): mixed
    {
        return (property_exists($this->coupon, 'expired_at')) ? str_replace(' ', 'T', $this->coupon->expired_at) : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return CouponData
     */
    public function getCouponData(): CouponData
    {
        return $this->couponData;
    }

    /**
     * @param CouponData $couponData
     * @return $this
     */
    public function setCouponData(CouponData $couponData): static
    {
        $this->couponData = $couponData;
        return $this;
    }

    /**
     * @return \stdClass
     */
    public function getCoupon(): \stdClass
    {
        return $this->coupon;
    }

    /**
     * @param \stdClass $coupon
     */
    public function setCoupon(\stdClass $coupon): void
    {
        $this->coupon = $coupon;
    }
}