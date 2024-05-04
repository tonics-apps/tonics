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
        $result = null;
        db(onGetDB: function ($db) use (&$result){
            $table = Tables::getTable(Tables::TRACKS);
            $result = $db->row("SELECT COUNT(*) as count FROM $table WHERE track_status = 1 AND NOW() >= created_at");
        });

        return (isset($result->count)) ? $result->count : 0;
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
                    $table = Tables::getTable(Tables::TRACKS);
                    $select = "CONCAT_WS( '/', '/tracks', slug_id, track_slug ) AS `_link`, image_url as '_image', updated_at as '_lastmod'";
                    $cbData = null;
                    db(onGetDB: function ($db) use ($offset, $perPage, $table, $select, &$cbData){
                        $cbData = $db->run(<<<SQL
SELECT $select FROM $table WHERE track_status = 1 AND NOW() >= created_at ORDER BY updated_at DESC LIMIT ? OFFSET ? 
SQL, $perPage, $offset);
                    });
                    return $cbData;

                }, perPage: $this->getLimit());
        });

        return $data->data ?? [];
    }
}