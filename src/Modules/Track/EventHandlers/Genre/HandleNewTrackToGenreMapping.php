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

use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnPostCreate;
use App\Modules\Track\Events\OnTrackCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewTrackToGenreMapping implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {

        /**
         * @var OnTrackCreate $event
         */
        $toInsert = [];
        foreach ($event->getTrackFKGenreIDS() as $genreID){
            $toInsert[] = [
                'fk_genre_id' => $genreID,
                'fk_track_id' => $event->getTrackID(),
            ];
        }

        db(onGetDB: function ($db) use ($toInsert, $event){
            $table = $event->getTrackData()->getTrackToGenreTable();
            $db->Insert($table, $toInsert);
        });
    }
}