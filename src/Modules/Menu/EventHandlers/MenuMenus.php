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

namespace App\Modules\Menu\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
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

            $tree->add(AdminMenuHelper::MENU, [
                'mt_name' => 'Menu',
                'mt_url_slug' => route('menus.index'),
                'mt_icon' => helper()->getIcon('menu', 'icon:admin')
            ]);

            $tree->add(AdminMenuHelper::MENU_NEW, [
                'mt_name' => 'New Menu',
                'mt_url_slug' => route('menus.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

        },['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_MENU])], AdminMenuHelper::PRIORITY_MEDIUM);
    }
}