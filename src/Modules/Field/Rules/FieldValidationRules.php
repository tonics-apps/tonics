<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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