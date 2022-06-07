<?php

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
        $slugGen = $event->getTrackID() . random_int(PHP_INT_MIN, PHP_INT_MAX). hrtime(true);
        $slugGen = hash('xxh3', $slugGen);
        $trackToUpdate = $event->getTrackData()->createTrack(['track_slug']);
        $trackToUpdate['slug_id'] = $slugGen;
        $event->getTrackData()->updateWithCondition($trackToUpdate, ['track_id' => $event->getTrackID()], $event->getTrackData()->getTrackTable());
    }
}