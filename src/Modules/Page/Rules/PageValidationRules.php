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

namespace App\Modules\Page\Rules;

use App\Modules\Core\Library\Tables;

trait PageValidationRules
{
    /**
     * @throws \Exception
     */
    public function pageStoreRule(): array
    {
        $postSlugUnique = Tables::getTable(Tables::PAGES) .':page_slug';
        return [
            'page_title' => ['required', 'string'],
            'page_slug' => ['required', 'string', 'unique' => [
                    $postSlugUnique => input()->fromPost()->retrieve('page_slug', '')]
            ],
            'page_status' => ['required', 'numeric'],
            'created_at' => ['required', 'string']
        ];
    }

    /**
     * @throws \Exception
     */
    public function pageUpdateRule(): array
    {
        $postSlugUnique = Tables::getTable(Tables::PAGES) .':page_slug:page_id';
        return [
            'page_title' => ['required', 'string'],
            'page_slug' => ['required', 'string',
                'unique' => [
                    $postSlugUnique => input()->fromPost()->retrieve('page_id', '')
                ]
            ],
            'page_status' => ['required', 'numeric'],
            'created_at' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function pageUpdateMultipleRule(): array
    {
        return [
            'page_id' => ['numeric'],
            'page_title' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
        ];
    }

}