<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page\EventHandlers;

use App\Modules\Core\Events\Tools\Sitemap\AbstractSitemapInterface;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PageSitemap extends AbstractSitemapInterface implements HandlerInterface
{
    /**
     * @throws \Exception
     */
    public function getSitemapDataCount(): ?int
    {
        if (is_null($this->dataCount)){
            $result = null;
            db(onGetDB: function ($db) use (&$result){
                $table = Tables::getTable(Tables::PAGES);
                $result = $db->row("SELECT COUNT(*) as count FROM $table WHERE page_status = 1 AND NOW() >= created_at");
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
                        $table = Tables::getTable(Tables::PAGES);
                        $cbData = $db->run(<<<SQL
SELECT page_slug AS `_link`, updated_at as '_lastmod'
FROM $table WHERE page_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
                    });
                    return $cbData;
                }, perPage: $this->getLimit());
        });

        return $data->data;
    }
}