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

use App\Modules\Field\Interfaces\FieldValueSanitizationInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddFieldSanitization implements EventInterface
{
    private array $fieldsSanitization = [];

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    public function addField(FieldValueSanitizationInterface $fieldValueSanitization): static
    {
        $this->fieldsSanitization[$fieldValueSanitization->sanitizeName()] = $fieldValueSanitization;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldsSanitization(): array
    {
        return $this->fieldsSanitization;
    }

    /**
     * @param array $fieldsSanitization
     * @return OnAddFieldSanitization
     */
    public function setFieldsSanitization(array $fieldsSanitization): OnAddFieldSanitization
    {
        $this->fieldsSanitization = $fieldsSanitization;
        return $this;
    }
}