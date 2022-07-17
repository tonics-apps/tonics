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

    public function addField(string $name, string $slug, string $icon = null): static
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
      usort($this->fields, function ($a, $b){
            return strcasecmp($a['name'], $b['name']);
        });
        return $this->fields;
    }
}