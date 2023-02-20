<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Jobs;

use App\Modules\Core\Library\ConsoleColor;
use App\Modules\Core\Library\JobSystem\AbstractJobInterface;
use App\Modules\Core\Library\JobSystem\JobHandlerInterface;
use App\Modules\Track\Controllers\TracksController;
use App\Modules\Track\Data\TrackData;

class TrackItemImport extends AbstractJobInterface implements JobHandlerInterface
{
    use ConsoleColor;

    private TracksController $tracksController;

    public function __construct(TracksController $tracksController)
    {
        $this->tracksController = $tracksController;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function handle(): void
    {
        $track = $this->getDataAsArray();
        if (isset($track['track_title'])) {
            $slug = helper()->slug($track['track_title']);
            if (!isset($track['track_slug'])){
                $track['track_slug'] = $slug;
            }
        }

        try {
            $this->getTrackController()->setIsUserInCLI(True);
            $_POST = $track;
            $trackData = null;
            db(onGetDB: function ($db) use($track, &$trackData) {
                $trackData = db()->Select("track_slug, track_id")->From(TrackData::getTrackTable())
                    ->WhereEquals('track_slug', $track['track_slug'])
                    ->FetchFirst();
            });

            if (isset($trackData->track_slug)) {
                $_POST['track_id'] = $trackData->track_id;
                $this->getTrackController()->update($trackData->track_slug);
                $this->successMessage($track['track_title'] . " [Track Updated] ");
            } else {
                $this->getTrackController()->store();
                $this->successMessage($track['track_title'] . " [Track Created] ");
            }
        } catch (\Throwable $exception){
            $title = $track['track_title'] ?? '';
            // Log..
            throw new \Exception("Failed To Import Track Item ($title) - An Error Occurred - {$exception->getMessage()}");
        }

    }

    /**
     * @return TracksController
     */
    public function getTrackController(): TracksController
    {
        return $this->tracksController;
    }

    /**
     * @param TracksController $trackController
     */
    public function setCouponController(TracksController $trackController): void
    {
        $this->tracksController = $trackController;
    }
}