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

namespace App\Modules\Media\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class MediaMenus implements HandlerInterface
{
    /**
     * @param object $event
     *
     * @throws \Exception|\Throwable
     */
    public function handleEvent (object $event): void
    {
        \tree()->group('', function (Tree $tree) {

            $tree->add(AdminMenuHelper::MEDIA,
                [
                    'mt_name' => 'Media', 'mt_url_slug' => '#0', 'mt_icon' => helper()->getIcon('play', 'icon:admin'),
                ]);
            $tree->add(AdminMenuHelper::FILE_MANAGER,
                [
                    'mt_name' => 'File Manager', 'mt_url_slug' => route('media.show'), 'mt_icon' => helper()->getIcon('media-file', 'icon:admin'),
                ]);

            $tree->add(AdminMenuHelper::LICENSE,
                [
                    'mt_name' => 'License', 'mt_url_slug' => route('admin.licenses.index'), 'mt_icon' => helper()->getIcon('license', 'icon:admin'),
                ]);
            $tree->add(AdminMenuHelper::LICENSE_NEW,
                [
                    'mt_name' => 'New License', 'mt_url_slug' => route('admin.licenses.create'), 'mt_icon' => helper()->getIcon('plus', 'icon:admin'),
                ]);

            $tree->add(AdminMenuHelper::LICENSE_EDIT,
                [
                    'mt_name' => 'Edit License', 'mt_url_slug' => '/admin/tools/license/:license/edit', 'ignore' => true,
                ]);

            $tree->add(AdminMenuHelper::LICENSE_ITEMS_EDIT,
                [
                    'mt_name' => 'Edit License Items', 'mt_url_slug' => '/admin/tools/license/items/:license/builder', 'ignore' => true,
                ]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_MEDIA])]);
    }
}