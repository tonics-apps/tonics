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

class OnAfterPreSavePostEditorFieldItems implements EventInterface
{

    private array $fieldSettings;

    public function __construct(array $fieldSettings)
    {
        $this->fieldSettings = $fieldSettings;
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
    public function getFieldSettings(): array
    {
        return $this->fieldSettings;
    }

    /**
     * @param array $fieldSettings
     */
    public function setFieldSettings(array $fieldSettings): void
    {
        $this->fieldSettings = $fieldSettings;
    }


}