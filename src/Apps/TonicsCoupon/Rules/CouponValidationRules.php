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
            'fk_coupon_type_id' => ['required', 'array'],
        ];
    }

}