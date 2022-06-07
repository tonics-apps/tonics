<?php

namespace App\Modules\Page\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnPageDefaultField implements EventInterface
{

    private array $pageDefaultFieldSlug = [];

    public function addDefaultField($slug): static
    {
        $this->pageDefaultFieldSlug[] = $slug;
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
    public function getPostDefaultFieldSlug(): array
    {
        return $this->pageDefaultFieldSlug;
    }

    /**
     * @param array $pageDefaultFieldSlug
     * @return OnPageDefaultField
     */
    public function setPostDefaultFieldSlug(array $pageDefaultFieldSlug): OnPageDefaultField
    {
        $this->pageDefaultFieldSlug = $pageDefaultFieldSlug;
        return $this;
    }
}