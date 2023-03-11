<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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