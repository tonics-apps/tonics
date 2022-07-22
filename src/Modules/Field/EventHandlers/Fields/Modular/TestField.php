<?php

namespace App\Modules\Field\EventHandlers\Fields\Modular;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TestField implements FieldTemplateFileInterface
{

    public function handleFieldLogic(OnFieldMetaBox $event = null, $data = null): string
    {
        return '<p>' . getPostData()['quick_text'] . '<br>' . getPostData()['date_test'] . '</p>';
    }


    public function name(): string
    {
        return 'A Test Field';
    }
}