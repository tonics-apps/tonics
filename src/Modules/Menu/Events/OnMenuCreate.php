<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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