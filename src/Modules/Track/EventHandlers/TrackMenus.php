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

namespace App\Modules\Track\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class TrackMenus implements HandlerInterface
{
    /**
     * @param object $event
     *
     * @throws \Exception|\Throwable
     */
    public function handleEvent (object $event): void
    {
        tree()->group('', function (Tree $tree) {

            $tree->add(AdminMenuHelper::TRACK, [
                'mt_name'     => 'Track',
                'mt_url_slug' => route('tracks.index'),
                'mt_icon'     => helper()->getIcon('step-forward'),
            ]);

            $tree->add(AdminMenuHelper::TRACK_NEW, [
                'mt_name'     => 'New Track',
                'mt_url_slug' => route('tracks.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);

            $tree->add(AdminMenuHelper::TRACK_EDIT, [
                'mt_name'     => 'Edit Track',
                'mt_url_slug' => '/admin/tracks/:track/edit',
                'ignore'      => true,
            ]);

            $tree->add(AdminMenuHelper::TRACK_CATEGORY, [
                'mt_name'     => 'Track Category',
                'mt_url_slug' => route('tracks.category.index'),
                'mt_icon'     => helper()->getIcon('category', 'icon:admin'),
            ]);
            $tree->add(AdminMenuHelper::TRACK_CATEGORY_NEW, [
                'mt_name'     => 'New Track Category',
                'mt_url_slug' => route('tracks.category.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);

            $tree->add(AdminMenuHelper::TRACK_CATEGORY_EDIT, [
                'mt_name'     => 'Edit Track Category',
                'mt_url_slug' => '/admin/tracks/category/:category/edit',
                'ignore'      => true,
            ]);

            $tree->add(AdminMenuHelper::GENRE, [
                'mt_name'     => 'Genres',
                'mt_url_slug' => route('genres.index'),
                'mt_icon'     => helper()->getIcon('archive', 'icon:admin'),
            ]);
            $tree->add(AdminMenuHelper::GENRE_NEW, [
                'mt_name'     => 'New Genre',
                'mt_url_slug' => route('genres.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);

            $tree->add(AdminMenuHelper::GENRE_EDIT, [
                'mt_name'     => 'Edit Genre',
                'mt_url_slug' => '/admin/genres/:genre/edit',
                'ignore'      => true,
            ]);

            $tree->add(AdminMenuHelper::ARTIST, [
                'mt_name'     => 'Artist',
                'mt_url_slug' => route('artists.index'),
                'mt_icon'     => helper()->getIcon('user-solid-circle', 'icon:admin'),
            ]);
            $tree->add(AdminMenuHelper::ARTIST_NEW, [
                'mt_name'     => 'New Artist',
                'mt_url_slug' => route('artists.create'),
                'mt_icon'     => helper()->getIcon('plus', 'icon:admin'),
            ]);

            $tree->add(AdminMenuHelper::ARTIST_EDIT, [
                'mt_name'     => 'Edit Artist',
                'mt_url_slug' => '/admin/artists/:artist/edit',
                'ignore'      => true,
            ]);

            $tree->add(AdminMenuHelper::TRACK_LICENSE, ['mt_name' => 'License', 'mt_url_slug' => route('admin.licenses.index'), 'mt_icon' => helper()->getIcon('license', 'icon:admin')]);
            $tree->add(AdminMenuHelper::TRACK_LICENSE_NEW, ['mt_name' => 'New License', 'mt_url_slug' => route('admin.licenses.create'), 'mt_icon' => helper()->getIcon('plus', 'icon:admin')]);

            $tree->add(AdminMenuHelper::TRACK_LICENSE_EDIT, [
                'mt_name'     => 'Edit License',
                'mt_url_slug' => '/admin/tools/license/:license/edit',
                'ignore'      => true,
            ]);

            $tree->add(AdminMenuHelper::TRACK_LICENSE_ITEMS_EDIT, [
                'mt_name'     => 'Edit License Items',
                'mt_url_slug' => '/admin/tools/license/items/:license/builder',
                'ignore'      => true,
            ]);

        }, ['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_TRACK])]);
    }
}