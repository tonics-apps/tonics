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

abstract class AbstractDataTableFieldInterface
{
    public function header(): array
    {
        return [
            'type' => 'TEXT',
            'title' => 'Unknown Header',
            'minmax' => '150px, 1fr',
        ];
    }

    abstract public function renderDataTableView(OnFieldMetaBox $event, $data): string;


}