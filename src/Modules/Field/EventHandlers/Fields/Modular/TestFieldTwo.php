<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TestFieldTwo implements FieldTemplateFileInterface
{

    public function handleFieldLogic(OnFieldMetaBox $event = null, $data = null): string
    {
        return "Test Two";
    }

    public function name(): string
    {
        return 'A Test Field Two';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }
}