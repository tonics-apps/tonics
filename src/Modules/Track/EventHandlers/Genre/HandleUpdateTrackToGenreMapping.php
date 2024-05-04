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

namespace App\Modules\Track\EventHandlers\Genre;

use App\Modules\Track\Events\OnTrackUpdate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleUpdateTrackToGenreMapping implements HandlerInterface
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
        foreach ($event->getTrackFKGenreIDS() as $genreID){
            $toInsert[] = [
                'fk_genre_id' => $genreID,
                'fk_track_id' => $event->getTrackID(),
            ];
        }

        $table = $event->getTrackData()->getTrackToGenreTable();
        db(onGetDB: function ($db) use ($event, $table, $toInsert){
            $db->FastDelete($table, db()->WhereIn('fk_track_id', $event->getTrackID()));
            $db->Insert($table, $toInsert);
        });
    }
}