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