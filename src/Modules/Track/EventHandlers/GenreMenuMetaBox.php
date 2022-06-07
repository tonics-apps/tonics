<?php

namespace App\Modules\Track\EventHandlers;

use App\Modules\Menu\Events\OnMenuMetaBox;
use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class GenreMenuMetaBox implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        $trackData = new TrackData();
        $paginationInfo = $trackData->generatePaginationData(
            $trackData->getGenrePaginationColumn(),
            'genre_name',
            $trackData->getGenreTable());

        /** @var OnMenuMetaBox $event */
        $event->addMenuBox('Genres', helper()->getIcon('note'), $paginationInfo,
            function () use ($paginationInfo, $event) {
                return $event->moreMenuItems('Genres', $paginationInfo);
            });
    }
}