<?php

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