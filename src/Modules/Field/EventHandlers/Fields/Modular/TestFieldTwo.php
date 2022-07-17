<?php

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TestFieldTwo implements FieldTemplateFileInterface
{

    public function handleFieldLogic(OnFieldMetaBox $event, $data): string
    {
        return "Test Two";
    }

    public function name(): string
    {
        return 'A Test Field Two';
    }
}