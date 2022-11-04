<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\DefaultFieldHandlers;

use App\Modules\Field\Events\OnFieldMetaBox;
use App\Modules\Field\Interfaces\FieldTemplateFileInterface;

class TonicsOEmbedFieldHandler implements FieldTemplateFileInterface
{

    public function handleFieldLogic(OnFieldMetaBox $event = null, $fields = null): string
    {
        dd($fields);
    }

    public function name(): string
    {
        return 'OEmbed';
    }

    public function fieldSlug(): string
    {
       return 'oembed';
    }

    public function canPreSaveFieldLogic(): bool
    {
        return true;
    }
}