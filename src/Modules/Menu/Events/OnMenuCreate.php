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

namespace App\Modules\Menu\Events;

use App\Modules\Menu\Data\MenuData;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;
use stdClass;

class OnMenuCreate implements EventInterface
{

    private \stdClass $menu;
    private MenuData $menuData;

    /**
     * @param stdClass $menu
     * @param MenuData|null $menuData
     */
    public function __construct(stdClass $menu, MenuData $menuData = null)
    {
        $this->menu = $menu;
        if (isset($menu->created_at)){
            $this->menu->created_at = $this->getCatCreatedAt();
        }
        if (isset($menu->updated_at)){
            $this->menu->updated_at = $this->getCatUpdatedAt();
        }

        if ($menuData){
            $this->menuData = $menuData;
        }
    }

    public function getAll(): \stdClass
    {
        return $this->menu;
    }

    public function getAllToArray(): array
    {
        return (array)$this->menu;
    }

    public function getMenuID(): string|int
    {
        return (isset($this->menu->menu_id)) ? $this->menu->menu_id : '';
    }

    public function getMenuTitle(): string
    {
        return (isset($this->menu->menu_name)) ? $this->menu->menu_name : '';
    }

    public function getMenuSlug(): string
    {
        return (isset($this->menu->menu_slug)) ? $this->menu->menu_slug : '';
    }

    public function getCatCreatedAt(): string
    {
        return (isset($this->menu->created_at)) ? str_replace(' ', 'T', $this->menu->created_at) : '';
    }

    public function getCatUpdatedAt(): string
    {
        return (isset($this->menu->updated_at)) ? str_replace(' ', 'T', $this->menu->updated_at) : '';
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return MenuData
     */
    public function getMenuData(): MenuData
    {
        return $this->menuData;
    }

    /**
     * @param MenuData $menuData
     */
    public function setMenuData(MenuData $menuData): void
    {
        $this->menuData = $menuData;
    }
}