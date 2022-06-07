<?php

namespace App\Modules\Post\Events;

use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldUserForm;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnPostDefaultField implements EventInterface
{

    private array $postDefaultFieldSlug = [];

    public function addDefaultField($slug): static
    {
        $this->postDefaultFieldSlug[] = $slug;
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
        return $this->postDefaultFieldSlug;
    }

    /**
     * @param array $postDefaultFieldSlug
     * @return OnPostDefaultField
     */
    public function setPostDefaultFieldSlug(array $postDefaultFieldSlug): OnPostDefaultField
    {
        $this->postDefaultFieldSlug = $postDefaultFieldSlug;
        return $this;
    }
}