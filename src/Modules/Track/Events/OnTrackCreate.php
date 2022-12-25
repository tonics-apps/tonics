<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Events;

use App\Modules\Track\Data\TrackData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use stdClass;

class OnTrackCreate implements EventInterface
{
    private stdClass $track;
    private TrackData $trackData;

    /**
     * @param stdClass $track
     * @param TrackData|null $trackData
     */
    public function __construct(stdClass $track, TrackData $trackData = null)
    {
        $this->track = $track;
        if (property_exists($track, 'created_at')){
            $this->track->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($track, 'updated_at')){
            $this->track->updated_at = $this->getCatUpdatedAt();
        }

        if ($trackData){
            $this->trackData = $trackData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->track;
    }

    public function getAllToArray(): array
    {
        return (array)$this->track;
    }

    public function getTrackID(): string|int
    {
        return (isset($this->track->track_id)) ? $this->track->track_id : '';
    }

    public function getTrackSlugID(): string|int
    {
        return (isset($this->track->slug_id)) ? $this->track->slug_id : '';
    }

    public function getTrackName(): string
    {
        return (isset($this->track->track_title)) ? $this->track->track_title : '';
    }

    public function getTrackSlug(): string
    {
        return (isset($this->track->track_slug)) ? $this->track->track_slug : '';
    }

    public function getTrackContent(): string
    {
        return (isset($this->track->track_content)) ? $this->track->track_content : '';
    }

    public function getTrackImageURL(): string
    {
        return (isset($this->track->image_url)) ? $this->track->image_url : '';
    }

    public function getTrackAudioURL(): string
    {
        return (isset($this->track->audio_url)) ? $this->track->audio_url : '';
    }

    public function getTrackPlays(): string|int
    {
        return (isset($this->track->track_plays)) ? $this->track->track_plays : '';
    }

    public function getTrackBPM(): string|int
    {
        return (isset($this->track->track_bpm)) ? $this->track->track_bpm : '';
    }

    public function getTrackStatus(): string|int
    {
        return (isset($this->track->track_status)) ? $this->track->track_status : '';
    }

    public function getTrackFKGenreIDS($name = 'fk_genre_id'): array
    {
        $genreIDS = (property_exists($this->track, $name)) ? $this->track->{$name} : [];
        if (!is_array($genreIDS) && !empty($genreIDS)){
            $genreIDS = [$genreIDS];
        }
        return $genreIDS;
    }

    public function getTrackCatIDS($name = 'fk_track_cat_id'): array
    {
        $trackCatIDS = (property_exists($this->track, $name)) ? $this->track->{$name} : [];
        if (!is_array($trackCatIDS) && !empty($trackCatIDS)){
            $trackCatIDS = [$trackCatIDS];
        }
        return $trackCatIDS;
    }

    public function getTrackFKArtistID(): string|int
    {
        return (isset($this->track->fk_artist_id)) ? $this->track->fk_artist_id : '';
    }

    public function getTrackFKLicenseID(): string|int
    {
        return (isset($this->track->fk_license_id)) ? $this->track->fk_license_id : '';
    }

    public function getTrackLicenseAttr(): mixed
    {
        return (isset($this->track->license_attr)) ? json_decode($this->track->license_attr) : '';
    }

    public function getTrackLicenseAttrToIDLink(): mixed
    {
        return (isset($this->track->license_attr_id_link)) ? json_decode($this->track->license_attr_id_link) : '';
    }

    public function getTrackGenreName(): string
    {
        return (isset($this->track->genre_name)) ? $this->track->genre_name : '';
    }

    public function getTrackGenreSlug(): string
    {
        return (isset($this->track->genre_slug)) ? $this->track->genre_slug : '';
    }

    public function getCatCreatedAt(): string
    {
        return (isset($this->track->created_at)) ? str_replace(' ', 'T', $this->track->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (isset($this->track->updated_at)) ? str_replace(' ', 'T', $this->track->updated_at) : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return TrackData
     */
    public function getTrackData(): TrackData
    {
        return $this->trackData;
    }

    /**
     * @param TrackData $trackData
     * @return OnTrackCreate
     */
    public function setTrackData(TrackData $trackData): OnTrackCreate
    {
        $this->trackData = $trackData;
        return $this;
    }

}