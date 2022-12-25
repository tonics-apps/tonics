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

class OnTrackCategoryCreate implements EventInterface
{

    private \stdClass $category;
    private TrackData $trackData;

    public function __construct(\stdClass $category, TrackData $trackData = null)
    {
        $this->category = $category;
        if (property_exists($category, 'created_at')){
            $this->category->created_at = $this->getCatCreatedAt();
        }
        if (property_exists($category, 'updated_at')){
            $this->category->updated_at = $this->getCatUpdatedAt();
        }

        if ($trackData){
            $this->trackData = $trackData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->category;
    }

    public function getAllToArray(): array
    {
        return (array)$this->category;
    }

    public function getCatID(): string|int
    {
        return (property_exists($this->category, 'track_cat_id')) ? $this->category->track_cat_id : '';
    }

    public function getSlugID(): mixed
    {
        return (property_exists($this->category, 'slug_id')) ? $this->category->slug_id : '';
    }

    public function getCatStatus(): string|int
    {
        return (property_exists($this->category, 'track_cat_status')) ? $this->category->track_cat_status : '';
    }

    public function getCatParentID(): mixed
    {
        return (property_exists($this->category, 'track_cat_parent_id')) ? $this->category->track_cat_parent_id : '';
    }

    public function getCatName(): string
    {
        return (property_exists($this->category, 'track_cat_name')) ? $this->category->track_cat_name : '';
    }

    public function getCatSlug(): string
    {
        return (property_exists($this->category, 'track_cat_slug')) ? $this->category->track_cat_slug : '';
    }

    public function getCatCreatedAt(): mixed
    {
        return (property_exists($this->category, 'created_at')) ? str_replace(' ', 'T', $this->category->created_at) : '';
    }

    public function getCatUpdatedAt(): mixed
    {
        return (property_exists($this->category, 'updated_at')) ? str_replace(' ', 'T', $this->category->updated_at) : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return trackData
     */
    public function getTrackData(): trackData
    {
        return $this->trackData;
    }

    /**
     * @param trackData $trackData
     */
    public function setTrackData(trackData $trackData): void
    {
        $this->trackData = $trackData;
    }

    /**
     * @return \stdClass
     */
    public function getCategory(): \stdClass
    {
        return $this->category;
    }

    /**
     * @param \stdClass $category
     */
    public function setCategory(\stdClass $category): void
    {
        $this->category = $category;
    }
}