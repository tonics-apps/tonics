<?php

namespace App\Modules\Field\Interfaces;

use App\Modules\Field\Events\OnFieldMetaBox;

interface FieldTemplateFileInterface
{
    public function handleFieldLogic(OnFieldMetaBox $event = null, $data = null): string;

    public function name(): string;
}