<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\EventHandlers;

use App\Modules\Core\Library\AdminMenuPaths;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

class TrackMenus implements HandlerInterface
{
    /**
     * @param object $event
     * @throws \Exception|\Throwable
     */
    public function handleEvent(object $event): void
    {
        tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuPaths::TRACK, [
                'mt_name' => 'Track',
                'mt_url_slug' => route('tracks.index'),
                'mt_icon' => helper()->getIcon('step-forward')
            ]);
            $tree->add(AdminMenuPaths::TRACK_NEW, [
                'mt_name' => 'New Track',
                'mt_url_slug' => route('tracks.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);
            $tree->add(AdminMenuPaths::TRACK_CATEGORY, [
                'mt_name' => 'Track Category',
                'mt_url_slug' => route('tracks.category.index'),
                'mt_icon' => helper()->getIcon('category', 'icon:admin')
            ]);
            $tree->add(AdminMenuPaths::TRACK_CATEGORY_NEW, [
                'mt_name' => 'New Track Category',
                'mt_url_slug' => route('tracks.category.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(AdminMenuPaths::GENRE, [
                'mt_name' => 'Genres',
                'mt_url_slug' => route('genres.index'),
                'mt_icon' => helper()->getIcon('archive', 'icon:admin')
            ]);
            $tree->add(AdminMenuPaths::GENRE_NEW, [
                'mt_name' => 'New Genre',
                'mt_url_slug' => route('genres.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(AdminMenuPaths::ARTIST, [
                'mt_name' => 'Artist',
                'mt_url_slug' => route('artists.index'),
                'mt_icon' => helper()->getIcon('user-solid-circle', 'icon:admin')
            ]);
            $tree->add(AdminMenuPaths::ARTIST_NEW, [
                'mt_name' => 'New Artist',
                'mt_url_slug' => route('artists.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(AdminMenuPaths::TRACK_LICENSE, ['mt_name' => 'License','mt_url_slug' => route('licenses.index'),'mt_icon' => helper()->getIcon('license','icon:admin') ]);
            $tree->add(AdminMenuPaths::TRACK_LICENSE_NEW, ['mt_name' => 'New License','mt_url_slug' => route('licenses.create'),'mt_icon' => helper()->getIcon('plus', 'icon:admin') ]);

        },['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_TRACK])]);
    }
}