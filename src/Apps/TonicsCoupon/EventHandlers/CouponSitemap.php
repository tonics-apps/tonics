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

use App\Apps\TonicsCoupon\Controllers\CouponSettingsController;
use App\Apps\TonicsCoupon\TonicsCouponActivator;
use App\Modules\Core\Events\Tools\Sitemap\AbstractSitemapInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CouponSitemap extends AbstractSitemapInterface implements HandlerInterface
{
    /**
     * @throws \Exception
     */
    public function getDataCount(): ?int
    {
        if (is_null($this->dataCount)){
            $table = TonicsCouponActivator::couponTableName();
            $result = db()->row("SELECT COUNT(*) as count FROM $table WHERE coupon_status = 1 AND NOW() >= created_at");
            $this->setDataCount((isset($result->count)) ? (int)$result->count : 0);
        }

        return $this->dataCount;
    }

    /**
     * @throws \Exception
     */
    public function getData(): array
    {
        $data = db()->paginate(
            tableRows: $this->getDataCount(),
            callback: function ($perPage, $offset){
                $table = TonicsCouponActivator::couponTableName();
                $root = CouponSettingsController::getTonicsCouponRootPath();
                $select = "CONCAT_WS( '/', '/$root', slug_id, coupon_slug ) AS `_link`, image_url as '_image', updated_at as '_lastmod'";
                return db()->run(<<<SQL
SELECT $select FROM $table WHERE coupon_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
            }, perPage: $this->getLimit());

        return $data->data;
    }
}