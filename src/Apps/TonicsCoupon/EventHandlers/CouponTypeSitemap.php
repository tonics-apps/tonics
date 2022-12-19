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
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CouponTypeSitemap extends AbstractSitemapInterface implements HandlerInterface
{
    /**
     * @throws \Exception
     */
    public function getSitemapDataCount(): ?int
    {
        if (is_null($this->dataCount)){
            $table = TonicsCouponActivator::couponTypeTableName();
            $result = db()->row("SELECT COUNT(*) as count FROM $table WHERE coupon_type_status = 1 AND NOW() >= created_at");
            $this->setDataCount((isset($result->count)) ? (int)$result->count : 0);
        }

        return $this->dataCount;
    }

    /**
     * @throws \Exception
     */
    public function getSitemapData(): array
    {
        $data = db()->paginate(
            tableRows: $this->getSitemapDataCount(),
            callback: function ($perPage, $offset){
                $table = TonicsCouponActivator::couponTypeTableName();
                $root = CouponSettingsController::getTonicsCouponTypeRootPath();
                return db()->run(<<<SQL
SELECT CONCAT_WS( '/', '/$root', slug_id, coupon_type_slug ) AS `_link`, updated_at as '_lastmod'
FROM $table WHERE coupon_type_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
            }, perPage: $this->getLimit());
        return $data->data;
    }
}