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
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnCouponTypeCreate implements EventInterface
{

    private \stdClass $couponType;
    private CouponData $couponData;

    public function __construct(\stdClass $couponType, CouponData $couponData = null)
    {
        $this->couponType = $couponType;
        if (property_exists($couponType, 'created_at')){
            $this->couponType->created_at = $this->getCouponTypeCreatedAt();
        }

        if (property_exists($couponType, 'updated_at')){
            $this->couponType->updated_at = $this->getCouponTypeUpdatedAt();
        }

        if ($couponData){
            $this->couponData = $couponData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->couponType;
    }

    public function getAllToArray(): array
    {
        return (array)$this->couponType;
    }

    public function getCouponTypeID(): string|int
    {
        return (property_exists($this->couponType, 'coupon_type_id')) ? $this->couponType->coupon_type_id : '';
    }

    public function getSlugID(): mixed
    {
        return (property_exists($this->couponType, 'slug_id')) ? $this->couponType->slug_id : '';
    }

    public function getCouponTypeStatus(): string|int
    {
        return (property_exists($this->couponType, 'coupon_type_status')) ? $this->couponType->coupon_type_status : '';
    }

    public function getCouponTypeParentID(): mixed
    {
        return (property_exists($this->couponType, 'coupon_type_parent_id')) ? $this->couponType->coupon_type_parent_id : '';
    }

    public function getCouponTypeName(): string
    {
        return (property_exists($this->couponType, 'coupon_type_name')) ? $this->couponType->coupon_type_name : '';
    }

    public function getCouponTypeSlug(): string
    {
        return (property_exists($this->couponType, 'coupon_type_slug')) ? $this->couponType->coupon_type_slug : '';
    }

    public function getCouponTypeCreatedAt(): mixed
    {
        return (property_exists($this->couponType, 'created_at')) ? str_replace(' ', 'T', $this->couponType->created_at) : '';
    }

    public function getCouponTypeUpdatedAt(): mixed
    {
        return (property_exists($this->couponType, 'updated_at')) ? str_replace(' ', 'T', $this->couponType->updated_at) : '';
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
     */
    public function setCouponData(CouponData $couponData): void
    {
        $this->couponData = $couponData;
    }

    /**
     * @return \stdClass
     */
    public function getCouponType(): \stdClass
    {
        return $this->couponType;
    }

    /**
     * @param \stdClass $couponType
     */
    public function setCouponType(\stdClass $couponType): void
    {
        $this->couponType = $couponType;
    }
}