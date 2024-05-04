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
            $result = null;
            db(onGetDB: function ($db) use ($table, &$result){
                $result = $db->row("SELECT COUNT(*) as count FROM $table WHERE track_cat_status = 1 AND NOW() >= created_at");
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
                    $table = Tables::getTable(Tables::TRACK_CATEGORIES);
                    $cbData = null;
                    db(onGetDB: function ($db) use ($offset, $perPage, $table, &$cbData){
                        $cbData = $db->run(<<<SQL
SELECT CONCAT_WS( '/', '/track_categories', slug_id, track_cat_slug ) AS `_link`, updated_at as '_lastmod'
FROM $table WHERE track_cat_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
                    });
                    return $cbData;
                }, perPage: $this->getLimit());
        });

        return $data->data;
    }
}