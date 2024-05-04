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