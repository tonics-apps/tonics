<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnEditorFieldSelection implements EventInterface
{
    private array $fields = [];
    private int $fieldID = 0;

    const CATEGORY_TOOL = 'Tool';
    const CATEGORY_GENERAL = 'General';

    /**
     * @inheritDoc
     */
    public function event(): static
    {
       return $this;
    }

    public function addField(string $name, string $slug, string $icon = null, string $category = self::CATEGORY_GENERAL): static
    {
        $this->fields[$category][] = [
            'id' => $this->fieldID,
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon,
            'category' => $category,
        ];

        ++$this->fieldID;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        foreach ($this->fields as $fieldCategory){
            usort($fieldCategory, function ($a, $b){
                return strcasecmp($a['name'], $b['name']);
            });
        }
        return $this->fields;
    }
}