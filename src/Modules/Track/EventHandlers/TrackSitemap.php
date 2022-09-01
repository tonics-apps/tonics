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
    public function getDataCount(): ?int
    {
        $table = Tables::getTable(Tables::TRACKS);
        $result = db()->row("SELECT COUNT(*) as count FROM $table WHERE track_status = 1 AND NOW() >= created_at");
        return (isset($result->count)) ? $result->count : 0;
    }

    public function getData(): array
    {
        $trackData = new TrackData();
    }
}