<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Track\Events\AbstractClasses;

use App\Modules\Track\Data\TrackData;
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