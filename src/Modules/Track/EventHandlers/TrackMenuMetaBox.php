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

use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TrackMenuMetaBox implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $trackData = new TrackData();
        $paginationInfo = $trackData->generatePaginationData(
            $trackData->getTrackPaginationColumns(),
            'track_title',
            $trackData->getTrackTable());

        /** @var OnMenuMetaBox $event */
        $event->addMenuBox('Tracks', '', $paginationInfo,
            function () use ($paginationInfo, $event) {
                return $event->moreMenuItems('Tracks', $paginationInfo);
            }, dataCondition: function ($data){
                if (isset($data->track_status) && $data->track_status < 0){
                    return false;
                }
                return true;
            });
    }
}