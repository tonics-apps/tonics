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

use App\Modules\Post\Data\PostData;
use App\Modules\Track\Data\TrackData;
use App\Modules\Track\Events\OnGenreCreate;
use App\Modules\Track\Events\OnTrackCreate;
use stdClass;

abstract class GenreDataAccessor
{
    private stdClass $genre;
    private TrackData $trackData;

    /**
     * @param stdClass $genre
     * @param TrackData|null $trackData
     */
    public function __construct(stdClass $genre, TrackData $trackData = null)
    {
        $this->genre = $genre;
        if (property_exists($genre, 'created_at')){
            $this->genre->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($genre, 'updated_at')){
            $this->genre->updated_at = $this->getCatUpdatedAt();
        }

        if ($trackData){
            $this->trackData = $trackData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->genre;
    }

    public function getAllToArray(): array
    {
        return (array)$this->genre;
    }

    public function getGenreID(): string|int
    {
        return (isset($this->genre->genre_id)) ? $this->genre->genre_id : '';
    }

    public function getGenreName(): string
    {
        return (isset($this->genre->genre_name)) ? $this->genre->genre_name : '';
    }

    public function getGenreSlug(): string
    {
        return (isset($this->genre->genre_slug)) ? $this->genre->genre_slug : '';
    }

    public function getGenreDescription(): string
    {
        return (isset($this->genre->genre_description)) ? $this->genre->genre_description : '';
    }

    public function getGenreCanDelete(): string|int
    {
        return (isset($this->genre->can_delete)) ? $this->genre->can_delete : '';
    }

    public function event(): static
    {
        return $this;
    }

    public function getCatCreatedAt(): string
    {
        return (isset($this->genre->created_at)) ? str_replace(' ', 'T', $this->genre->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (isset($this->genre->updated_at)) ? str_replace(' ', 'T', $this->genre->updated_at) : '';
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
     * @return static
     */
    public function setTrackData(TrackData $trackData): static
    {
        $this->trackData = $trackData;
        return $this;
    }
}