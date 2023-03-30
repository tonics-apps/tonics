<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsAI\EventHandlers;

use App\Apps\TonicsToc\Controller\TonicsTocController;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TonicsAIOpenAIImageFieldHandler implements FieldTemplateFileInterface
{

    /**
     * @throws \Exception
     */
    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        if (isset($fields[0]) && $fields[0]->main_field_slug === $this->fieldSlug()){
            dd($fields[0]);
            return $this->getResult($fields[0]?->field_data);
        }
        return '';
    }

    public function fieldSlug(): string
    {
        return 'app-tonicsai-openai-image';
    }

    public function name(): string
    {
        return 'Image';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    public function getResult($fieldData): string
    {

    }
}