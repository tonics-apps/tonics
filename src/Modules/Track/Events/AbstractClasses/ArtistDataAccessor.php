<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Events\AbstractClasses;

use App\Modules\Track\Data\TrackData;
use stdClass;

abstract class ArtistDataAccessor
{
    private stdClass $artist;
    private TrackData $trackData;

    /**
     * @param stdClass $artist
     * @param TrackData|null $trackData
     */
    public function __construct(stdClass $artist, TrackData $trackData = null)
    {
        $this->artist = $artist;
        if (property_exists($artist, 'created_at')){
            $this->artist->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($artist, 'updated_at')){
            $this->artist->updated_at = $this->getCatUpdatedAt();
        }

        if ($trackData){
            $this->trackData = $trackData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->artist;
    }

    public function getAllToArray(): array
    {
        return (array)$this->artist;
    }

    public function getArtistID(): string|int
    {
        return (isset($this->artist->artist_id)) ? $this->artist->artist_id : '';
    }

    public function getArtistName(): string
    {
        return (isset($this->artist->artist_name)) ? $this->artist->artist_name : '';
    }

    public function getArtistSlug(): string
    {
        return (isset($this->artist->artist_slug)) ? $this->artist->artist_slug : '';
    }

    public function getArtistBio(): string
    {
        return (isset($this->artist->artist_bio)) ? $this->artist->artist_bio : '';
    }

    public function getArtistImageURL(): string
    {
        return (isset($this->artist->image_url)) ? $this->artist->image_url : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    public function getCatCreatedAt(): string
    {
        return (isset($this->artist->created_at)) ? str_replace(' ', 'T', $this->artist->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (isset($this->artist->updated_at)) ? str_replace(' ', 'T', $this->artist->updated_at) : '';
    }

    /**
     * @return TrackData
     */
    public function getArtistData(): TrackData
    {
        return $this->trackData;
    }

    /**
     * @param TrackData $artistData
     * @return static
     */
    public function setArtistData(TrackData $artistData): static
    {
        $this->trackData = $artistData;
        return $this;
    }
}