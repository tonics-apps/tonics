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

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TestField implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $data = null): string
    {
        if (FieldConfig::hasPreSavedFieldData()){
            return FieldConfig::getPreSavedFieldData();
        }
        return '<p>' . getPostData()['quick_text'] . '<br>' . getPostData()['date_test'] . '</p>';
    }


    public function name(): string
    {
        return 'A Test Field';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }
}