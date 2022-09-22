<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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
     * @throws \Exception
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