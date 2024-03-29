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

class HandleNewTrackSlugIDGeneration implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        ## The iteration should only go once, but in a unlikely case that a collision occur,
        # we try force updating the slugID until we max out 10 iterations
        ## but it should never happen even if you have 10Million posts
        $iterations = 10;
        for ($i = 0; $i < $iterations; ++$i) {
            try {
                $this->updateSlugID($event);
                break;
            } catch (\Exception){
                // Collision occur message
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function updateSlugID($event)
    {
        /**
         * @var OnTrackCreate $event
         */
        $slugGen = helper()->generateUniqueSlugID($event->getTrackID());
        $trackToUpdate = $event->getTrackData()->createTrack(['track_slug'], false);
        $trackToUpdate['slug_id'] = $slugGen;
        db(onGetDB: function ($db) use ($trackToUpdate, $event){
            $db->FastUpdate($event->getTrackData()->getTrackTable(), $trackToUpdate, db()->Where('track_id', '=', $event->getTrackID()));
        });
    }
}