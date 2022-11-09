<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\EventHandlers;

use App\Modules\Core\Events\Tools\Sitemap\AbstractSitemapInterface;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PostCategorySitemap extends AbstractSitemapInterface implements HandlerInterface
{
    /**
     * @throws \Exception
     */
    public function getDataCount(): ?int
    {
        if (is_null($this->dataCount)){
            $table = Tables::getTable(Tables::CATEGORIES);
            $result = db()->row("SELECT COUNT(*) as count FROM $table WHERE cat_status = 1 AND NOW() >= created_at");
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
                $table = Tables::getTable(Tables::CATEGORIES);
                return db()->run(<<<SQL
SELECT CONCAT_WS( '/', '/categories', slug_id, cat_slug ) AS `_link`, updated_at as '_lastmod'
FROM $table WHERE cat_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
            }, perPage: $this->getLimit());
        return $data->data;
    }
}