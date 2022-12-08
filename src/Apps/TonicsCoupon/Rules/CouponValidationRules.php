<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCoupon\Rules;

use App\Apps\TonicsCoupon\TonicsCouponActivator;

trait CouponValidationRules
{

    /**
     * @throws \Exception
     */
    public function couponTypeStoreRule(): array
    {
        $couponTypeSlugUnique = TonicsCouponActivator::couponTypeTableName() .':coupon_type_slug';
        return [
            'coupon_type_name' => ['required', 'string'],
            'coupon_type_content' => ['string'],
            'coupon_type_slug' => ['required', 'string', 'unique' => [
                $couponTypeSlugUnique => input()->fromPost()->retrieve('coupon_type_slug', '')]
            ],
            'created_at' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function couponTypeUpdateRule(): array
    {
        $couponTypeSlugUnique = TonicsCouponActivator::couponTypeTableName()  .':coupon_type_slug:coupon_type_id';
        return [
            'coupon_type_name' => ['required', 'string'],
            'coupon_type_content' => ['string'],
            'coupon_type_slug' => ['required', 'string', 'unique' => [
                $couponTypeSlugUnique => input()->fromPost()->retrieve('coupon_type_id', '')]
            ],
            'created_at' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function couponTypeUpdateMultipleRule(): array
    {
        return [
            'coupon_type_id' => ['numeric'],
            'coupon_type_name' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function couponStoreRule(): array
    {
        $couponSlugUnique = TonicsCouponActivator::couponTableName() .':coupon_slug';
        return [
            'coupon_name' => ['required', 'string'],
            'coupon_slug' => ['required', 'string', 'unique' => [
                    $couponSlugUnique => input()->fromPost()->retrieve('coupon_slug', '')]
            ],
            'coupon_content' => ['string'],
            'coupon_status' => ['required', 'numeric'],
            'user_id' => ['required', 'numeric'],
            'created_at' => ['required', 'string'],
            'image_url' => ['string'],
            'fk_coupon_type_id' => ['array'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function couponUpdateRule(): array
    {
        $couponSlugUnique = TonicsCouponActivator::couponTableName() .':coupon_slug:coupon_id';
        return [
            'coupon_name' => ['required', 'string'],
            'coupon_slug' => ['required', 'string',
                'unique' => [
                    $couponSlugUnique => input()->fromPost()->retrieve('coupon_id', '')]
            ],
            'coupon_content' => ['string'],
            'coupon_status' => ['required', 'numeric'],
            'user_id' => ['required', 'numeric'],
            'created_at' => ['required', 'string'],
            'image_url' => ['string'],
            'fk_coupon_type_id' => ['required', 'array'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function couponUpdateMultipleRule(): array
    {
        return [
            'coupon_id' => ['numeric'],
            'coupon_name' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
            'fk_coupon_type_id' => ['required', 'array'],
        ];
    }

}