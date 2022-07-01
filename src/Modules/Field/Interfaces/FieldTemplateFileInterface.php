<?php

namespace App\Modules\Field\Interfaces;

use App\Modules\Field\Events\OnFieldMetaBox;

interface FieldTemplateFileInterface
{
    public function handleFieldLogic(OnFieldMetaBox $event, $data): string;

    public function name(): string;
}