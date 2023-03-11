<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\EventHandlers\Artist;

use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\AbstractClasses\ArtistDataAccessor;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleArtistFilterTypeDeletion implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        db(onGetDB: function ($db){
            /** @var ArtistDataAccessor $event */
            $trackDefaultFiltersValueTable = TrackData::getTrackDefaultFiltersTable();
            $db->FastDelete($trackDefaultFiltersValueTable, db()->WhereEquals('tdf_type', 'artist')->WhereEquals('tdf_name', $event->getArtistSlug()));
        });
    }
}