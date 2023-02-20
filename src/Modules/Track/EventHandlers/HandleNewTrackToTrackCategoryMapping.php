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


use App\Modules\Track\Events\OnTrackCreate;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleNewTrackToTrackCategoryMapping implements HandlerInterface
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
        foreach ($event->getTrackCatIDS() as $catID){
            $toInsert[] = [
                'fk_track_cat_id' => $catID,
                'fk_track_id' => $event->getTrackID(),
            ];
        }

        $table = $event->getTrackData()->getTrackTracksCategoryTable();
        db()->Insert($table, $toInsert);

    }
}