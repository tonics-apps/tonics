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

namespace App\Modules\Menu\Rules;

use App\Modules\Core\Library\Tables;

trait MenuValidationRules
{
    /**
     * @throws \Exception
     */
    public function menuStoreRule(): array
    {
        $menuUniqueSlug = Tables::getTable(Tables::MENUS) .':menu_slug';
        return [
            'menu_name' => ['required', 'string'],
            'menu_slug' => ['required', 'string', 'unique' => [
                $menuUniqueSlug => input()->fromPost()->retrieve('menu_slug', '')]
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function menuUpdateRule(): array
    {
        $menuUniqueSlug = Tables::getTable(Tables::MENUS) .':menu_slug:menu_id';
        return [
            'menu_name' => ['required', 'string'],
            'menu_slug' => ['required', 'string', 'unique' => [
                $menuUniqueSlug => input()->fromPost()->retrieve('menu_id', '')]
            ],
        ];
    }

    /**
     * @return \string[][]
     */
    public function menuUpdateMultipleRule(): array
    {
        return [
            'menu_id' => ['numeric'],
            'menu_name' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
        ];
    }

    public function menuItemsStoreRule(): array
    {
        return [
            'menuSlug' => ['required', 'string'],
            'menuItems' => ['array']
        ];
    }

}