<?php
/*
 *     Copyright (c) 2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Menu\Controllers;

use App\Modules\Menu\Data\MenuData;

readonly class MenuControllerAPI
{
    public function __construct(private MenuData $menuData)
    {
    }

    /**
     * @param string $menuID
     *
     * @return void
     * @throws \Exception
     */
    public function MenuItems(string $menuID): void
    {
        try {
            helper()->onSuccess($this->menuData->getMenuItems($menuID));
        } catch (\Exception $exception) {
            helper()->onError($exception->getCode(), $exception->getMessage());
        }
    }

    /**
     * @param string $menuSlug
     *
     * @return void
     * @throws \Exception
     */
    public function MenuItemsFragment(string $menuSlug): void
    {
        try {
            helper()->onSuccess($this->menuData->getMenuFrontendFragment($menuSlug));
        } catch (\Exception $exception) {
            helper()->onError($exception->getCode(), $exception->getMessage());
        }
    }
}