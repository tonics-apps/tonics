<?php

namespace App\Modules\Field\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnEditorFieldSelection implements EventInterface
{
    private array $fields = [];

    /**
     * @inheritDoc
     */
    public function event(): static
    {
       return $this;
    }

    public function addField(string $name, string $slug, string $icon = ''): static
    {
        $this->fields[] = [
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}