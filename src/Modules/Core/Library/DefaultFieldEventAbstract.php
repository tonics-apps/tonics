<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library;

abstract class DefaultFieldEventAbstract
{
    private array $fieldSlug = [];
    private array $hiddenFieldSlug = [];

    public function addDefaultField($slug, bool $hideOnForm = false): static
    {
        if ($hideOnForm){
            $this->hiddenFieldSlug[] = $slug;
        } else {
            $this->fieldSlug[] = $slug;
        }
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
     * This gets the field slug but not the hidden slug
     * @return array
     */
    public function getFieldSlug(): array
    {
        return $this->fieldSlug;
    }

    /**
     * This gets the field slug (including the hidden slug)
     * @return array
     */
    public function getAllFieldSlug(): array
    {
        return [...$this->fieldSlug, ...$this->hiddenFieldSlug];
    }

    /**
     * @param array $fieldSlug
     * @return static
     */
    public function setFieldSlug(array $fieldSlug): static
    {
        $this->fieldSlug = $fieldSlug;
        return $this;
    }

    /**
     * @return array
     */
    public function getHiddenFieldSlug(): array
    {
        return $this->hiddenFieldSlug;
    }

    /**
     * @param array $hiddenFieldSlug
     */
    public function setHiddenFieldSlug(array $hiddenFieldSlug): void
    {
        $this->hiddenFieldSlug = $hiddenFieldSlug;
    }
}