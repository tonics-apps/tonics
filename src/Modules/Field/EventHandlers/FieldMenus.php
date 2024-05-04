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

namespace App\Modules\Field\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class FieldMenus implements HandlerInterface
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handleEvent(object $event): void
    {
        \tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuHelper::FIELD, ['mt_name' => 'Field','mt_url_slug' => route('fields.index'), 'mt_icon' => helper()->getIcon('widget','icon:admin') ]);
            $tree->add(AdminMenuHelper::FIELD_NEW, ['mt_name' => 'New Field','mt_url_slug' => route('fields.create'), 'mt_icon' => helper()->getIcon('plus','icon:admin') ]);;

            $tree->add(AdminMenuHelper::FIELD_EDIT, [
                'mt_name' => 'Edit Field',
                'mt_url_slug' => '/admin/tools/field/:field/edit',
                'ignore' => true,
            ]);

            $tree->add(AdminMenuHelper::FIELD_ITEMS_EDIT, [
                'mt_name' => 'Edit Field Items',
                'mt_url_slug' => '/admin/tools/field/items/:field/builder',
                'ignore' => true,
            ]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_FIELD])]);
    }
}