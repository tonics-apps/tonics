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
use App\Modules\Core\Events\Tools\Sitemap\OnAddSitemap;
use App\Modules\Core\Library\Tables;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TrackSitemap extends AbstractSitemapInterface implements HandlerInterface
{
    /**
     * @throws \Exception
     */
    public function getSitemapDataCount(): ?int
    {
        $table = Tables::getTable(Tables::TRACKS);
        $result = db()->row("SELECT COUNT(*) as count FROM $table WHERE track_status = 1 AND NOW() >= created_at");
        return (isset($result->count)) ? $result->count : 0;
    }

    /**
     * @throws \Exception
     */
    public function getSitemapData(): array
    {
        $data = db()->paginate(
            tableRows: $this->getSitemapDataCount(),
            callback: function ($perPage, $offset){
                $table = Tables::getTable(Tables::TRACKS);
                $select = "CONCAT_WS( '/', '/tracks', slug_id, track_slug ) AS `_link`, image_url as '_image', updated_at as '_lastmod'";
                return db()->run(<<<SQL
SELECT $select FROM $table WHERE track_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
            }, perPage: $this->getLimit());

        return $data->data ?? [];
    }
}