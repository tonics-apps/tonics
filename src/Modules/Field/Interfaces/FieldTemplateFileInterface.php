<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Interfaces;

use App\Modules\Field\Events\OnFieldMetaBox;

interface FieldTemplateFileInterface
{
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string;

    public function name(): string;

    public function fieldSlug(): string;

    public function canPreSaveFieldLogic(): bool;
}