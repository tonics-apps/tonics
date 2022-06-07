<?php

namespace App\Modules\Field\Rules;

use App\Library\Tables;

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

    public function fieldItemsStoreRule(): array
    {
        return [
            'fieldSlug' => ['required', 'string'],
            'fieldItems' => ['array'],
        ];
    }
}