<?php

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

    public function menuItemsStoreRule(): array
    {
        return [
            'menuSlug' => ['required', 'string'],
            'menuItems' => ['array']
        ];
    }

}