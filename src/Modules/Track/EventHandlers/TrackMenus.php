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

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TrackMenus implements HandlerInterface
{
    /**
     * @param object $event
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::getPermission(Roles::CAN_ACCESS_TRACK), $event->userRole()), function ($event) {

            return $event->addMenu(OnAdminMenu::TrackMenuID, 'Track', helper()->getIcon('step-forward'), route('tracks.create'), parent: OnAdminMenu::MediaMenuID)
                    ->addMenu(OnAdminMenu::TrackMenuID + 1, 'New Track', helper()->getIcon('plus', 'icon:admin'), route('tracks.create'), parent: OnAdminMenu::TrackMenuID)
                    ->addMenu(OnAdminMenu::TrackMenuID + 2, 'All Tracks', helper()->getIcon('playlist', 'icon:admin'), route('tracks.index'), parent: OnAdminMenu::TrackMenuID)

                ->addMenu(OnAdminMenu::TrackCategoryMenuID, 'Track Category', helper()->getIcon('category', 'icon:admin'), route('tracks.category.create'), parent:  OnAdminMenu::TrackMenuID)
                ->addMenu(OnAdminMenu::TrackCategoryMenuID + 1, 'New Track Category', helper()->getIcon('plus', 'icon:admin'), route('tracks.category.create'), parent: OnAdminMenu::TrackCategoryMenuID)
                ->addMenu(OnAdminMenu::TrackCategoryMenuID + 2, 'All Track Categories', helper()->getIcon('category', 'icon:admin'), route('tracks.category.index'), parent: OnAdminMenu::TrackCategoryMenuID)

                ->addMenu(OnAdminMenu::GenreMenuID, 'Genres', helper()->getIcon('archive', 'icon:admin'), route('genres.create'), parent: OnAdminMenu::MediaMenuID)
                    ->addMenu(OnAdminMenu::GenreMenuID + 1, 'New Genre', helper()->getIcon('plus', 'icon:admin'), route('genres.create'), parent: OnAdminMenu::GenreMenuID)
                    ->addMenu(OnAdminMenu::GenreMenuID + 2, 'All Genres', helper()->getIcon('archive', 'icon:admin'), route('genres.index'), parent: OnAdminMenu::GenreMenuID)

                ->addMenu(OnAdminMenu::ArtistMenuID, 'Artist', helper()->getIcon('user-solid-circle', 'icon:admin'), route('artists.create'), parent:  OnAdminMenu::MediaMenuID)
                ->addMenu(OnAdminMenu::ArtistMenuID + 1, 'New Artist', helper()->getIcon('plus', 'icon:admin'), route('artists.create'), parent: OnAdminMenu::ArtistMenuID)
                ->addMenu(OnAdminMenu::ArtistMenuID + 2, 'All Artist', helper()->getIcon('users', 'icon:admin'), route('artists.index'), parent: OnAdminMenu::ArtistMenuID);
        });
    }
}