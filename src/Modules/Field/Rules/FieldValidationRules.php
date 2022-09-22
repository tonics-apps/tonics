<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\Rules;

use App\Modules\Core\Library\Tables;

trait FieldValidationRules
{
    /**
     * @throws \Exception
     */
    public function fieldStoreRule(): array
    {
        $menuUniqueSlug = Tables::getTable(Tables::FIELD) .':field_slug';
        return [
            'field_name' => ['required', 'string'],
            'field_slug' => ['required', 'string', 'unique' => [
                $menuUniqueSlug => input()->fromPost()->retrieve('field_slug', '')]
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function fieldUpdateRule(): array
    {
        $fieldUniqueSlug = Tables::getTable(Tables::FIELD) .':field_slug:field_id';
        return [
            'field_name' => ['required', 'string'],
            'field_slug' => ['required', 'string', 'unique' => [
                $fieldUniqueSlug => input()->fromPost()->retrieve('field_id', '')]
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function fieldUpdateMultipleRule(): array
    {
        return [
            'field_id' => ['numeric'],
            'field_name' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
        ];
    }

    public function fieldItemsStoreRule(): array
    {
        return [
            'fieldSlug' => ['required', 'string'],
            'fieldItems' => ['array'],
        ];
    }
}