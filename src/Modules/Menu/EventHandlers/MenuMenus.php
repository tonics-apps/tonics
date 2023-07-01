<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Menu\EventHandlers;

use App\Modules\Core\Library\AdminMenuPaths;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class MenuMenus implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuPaths::MENU, [
                'mt_name' => 'Menu',
                'mt_url_slug' => route('menus.index'),
                'mt_icon' => helper()->getIcon('menu', 'icon:admin')
            ]);

            $tree->add(AdminMenuPaths::MENU_NEW, [
                'mt_name' => 'New Menu',
                'mt_url_slug' => route('menus.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

        },['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_MENU])], AdminMenuPaths::PRIORITY_MEDIUM);
    }
}