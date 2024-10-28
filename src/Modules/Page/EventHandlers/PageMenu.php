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

namespace App\Modules\Page\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class PageMenu implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception|\Throwable
     */
    public function handleEvent (object $event): void
    {

        tree()->group('', function (Tree $tree) {

            $tree->add(AdminMenuHelper::PAGE, [
                'mt_name'     => 'Pages',
                'mt_url_slug' => route('pages.index'),
                'mt_icon'     => helper()->getIcon('archive', 'icon:admin'),
            ]);

            $tree->add(AdminMenuHelper::PAGE_EDIT, [
                'mt_name'     => 'Edit Page',
                'mt_url_slug' => '/admin/pages/:page/edit',
                'ignore'      => true,
            ]);

            $tree->add(AdminMenuHelper::PAGE_NEW, [
                'mt_name'     => 'New Page',
                'mt_url_slug' => route('pages.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);
        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_PAGE])], AdminMenuHelper::PRIORITY_EXTREME);

    }
}