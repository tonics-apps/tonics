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

class OnGenreCreate implements EventInterface
{
    private stdClass $genre;
    private TrackData $genreData;

    /**
     * @param stdClass $artist
     * @param TrackData|null $trackData
     */
    public function __construct(stdClass $artist, TrackData $trackData = null)
    {
        $this->genre = $artist;
        if (property_exists($artist, 'created_at')){
            $this->genre->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($artist, 'updated_at')){
            $this->genre->updated_at = $this->getCatUpdatedAt();
        }

        if ($trackData){
            $this->genreData = $trackData;
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

    /**
     * @inheritDoc
     */
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
    public function getGenreData(): TrackData
    {
        return $this->genreData;
    }

    /**
     * @param TrackData $genreData
     * @return OnGenreCreate
     */
    public function setGenreData(TrackData $genreData): OnGenreCreate
    {
        $this->genreData = $genreData;
        return $this;
    }

}