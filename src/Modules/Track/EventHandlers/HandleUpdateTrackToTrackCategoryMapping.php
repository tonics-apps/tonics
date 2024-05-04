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

use App\Modules\Track\Events\OnTrackUpdate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleUpdateTrackToTrackCategoryMapping implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /**
         * @var OnTrackUpdate $event
         */
        $toInsert = [];
        foreach ($event->getTrackCatIDS() as $catID){
            $toInsert[] = [
                'fk_track_cat_id' => $catID,
                'fk_track_id' => $event->getTrackID(),
            ];
        }

        db(onGetDB: function ($db) use ($toInsert, $event) {
            $table = $event->getTrackData()->getTrackTracksCategoryTable();
            $db->FastDelete($table, db()->WhereIn('fk_track_id', $event->getTrackID()));
            $db->Insert($table, $toInsert);
        });
        
    }
}