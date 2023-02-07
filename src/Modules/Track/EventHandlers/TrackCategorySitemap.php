<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\EventHandlers;

use App\Modules\Core\Events\Tools\Sitemap\AbstractSitemapInterface;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TrackCategorySitemap extends AbstractSitemapInterface implements HandlerInterface
{
    /**
     * @throws \Exception
     */
    public function getSitemapDataCount(): ?int
    {
        if (is_null($this->dataCount)){
            $table = Tables::getTable(Tables::TRACK_CATEGORIES);
            $result = db()->row("SELECT COUNT(*) as count FROM $table WHERE track_cat_status = 1 AND NOW() >= created_at");
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
                $table = Tables::getTable(Tables::TRACK_CATEGORIES);
                return db()->run(<<<SQL
SELECT CONCAT_WS( '/', '/track_categories', slug_id, track_cat_slug ) AS `_link`, DATE_FORMAT(updated_at, '%Y-%m-%d') as '_lastmod'
FROM $table WHERE track_cat_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
            }, perPage: $this->getLimit());
        return $data->data;
    }
}