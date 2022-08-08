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

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnTrackDefaultField implements EventInterface
{
    private array $trackDefaultFieldSlug = [];

    public function addDefaultField($slug): static
    {
        $this->trackDefaultFieldSlug[] = $slug;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getTrackDefaultFieldSlug(): array
    {
        return $this->trackDefaultFieldSlug;
    }

    /**
     * @param array $trackDefaultFieldSlug
     * @return OnTrackDefaultField
     */
    public function setTrackDefaultFieldSlug(array $trackDefaultFieldSlug): OnTrackDefaultField
    {
        $this->trackDefaultFieldSlug = $trackDefaultFieldSlug;
        return $this;
    }
}