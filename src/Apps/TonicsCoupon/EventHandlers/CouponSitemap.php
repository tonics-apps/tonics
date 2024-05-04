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
    public function getSitemapDataCount(): ?int
    {
        if (is_null($this->dataCount)){
            $result = null;
            db(onGetDB: function ($db) use (&$result){
                $table = TonicsCouponActivator::couponTableName();
                $result = $db->row("SELECT COUNT(*) as count FROM $table WHERE coupon_status = 1 AND NOW() >= created_at");
            });

            $this->setDataCount((isset($result->count)) ? (int)$result->count : 0);
        }

        return $this->dataCount;
    }

    /**
     * @throws \Exception
     */
    public function getSitemapData(): array
    {
        $data = null;
        db(onGetDB: function ($db) use (&$data){
            $data = $db->paginate(
                tableRows: $this->getSitemapDataCount(),
                callback: function ($perPage, $offset){
                    $cbData = null;
                    db(onGetDB: function ($db) use ($offset, $perPage, &$cbData){
                        $table = TonicsCouponActivator::couponTableName();
                        $root = CouponSettingsController::getTonicsCouponRootPath();
                        $select = "CONCAT_WS( '/', '/$root', slug_id, coupon_slug ) AS `_link`, image_url as '_image', updated_at as '_lastmod'";
                        $cbData = $db->run(<<<SQL
SELECT $select FROM $table WHERE coupon_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
                    });
                }, perPage: $this->getLimit());
        });

        return $data->data;
    }

    /**
     * @throws \Exception
     */
    public function getSitemapNewsDataCount(): ?int
    {
        if (is_null($this->dataCount)){
            $result = null;
            db(onGetDB: function ($db) use (&$result){
                $table = TonicsCouponActivator::couponTableName();
                $result = $db->row("SELECT COUNT(*) as count FROM $table WHERE coupon_status = 1 
                                      AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY) 
                                      AND TRIM(JSON_UNQUOTE(JSON_EXTRACT(field_settings, '$.app_tonics_seo_structured_data_article_article_type'))) = 'NewsArticle'
                                      ");
            });
            $this->setDataCount((isset($result->count)) ? (int)$result->count : 0);
        }

        return $this->dataCount;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSitemapNewsData(): array
    {
        $data = null;
        db(onGetDB: function ($db) use (&$data){
            $data = $db->paginate(
                tableRows: $this->getSitemapNewsDataCount(),
                callback: function ($perPage, $offset){
                    $cbData = null;
                    db(onGetDB: function ($db) use ($offset, $perPage, &$cbData){
                        $table = TonicsCouponActivator::couponTableName();
                        $root = CouponSettingsController::getTonicsCouponRootPath();
                        $select = "CONCAT_WS( '/', '/$root', slug_id, coupon_slug ) AS `_link`, 
                image_url AS '_image', 
                coupon_name AS '_title', 
                DATE_FORMAT(created_at, '%Y-%m-%d') AS '_published_date', 
                DATE_FORMAT(updated_at, '%Y-%m-%d') AS '_lastmod'";
                        $cbData = $db->run(<<<SQL
SELECT $select FROM $table WHERE coupon_status = 1 
                          AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY) 
                          AND TRIM(JSON_UNQUOTE(JSON_EXTRACT(field_settings, '$.app_tonics_seo_structured_data_article_article_type'))) = 'NewsArticle'
                          ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
                    });
                }, perPage: $this->getLimit());
        });

        return $data->data ?? [];
    }
}