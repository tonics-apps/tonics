<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Track\EventHandlers;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class MediaMenus implements HandlerInterface
{
    /**
     * @param object $event
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_TRACK, $event->userRole()), function ($event) {

            return $event->addMenu(OnAdminMenu::MediaMenuID, 'Media', helper()->getIcon('play', 'icon:admin'), '#0')
                ->addMenu(OnAdminMenu::TrackMenuID, 'Track', helper()->getIcon('step-forward'), route('tracks.create'), parent:  OnAdminMenu::MediaMenuID)
                    ->addMenu(OnAdminMenu::TrackMenuID + 1, 'New Track', helper()->getIcon('plus', 'icon:admin'), route('tracks.create'), parent: OnAdminMenu::TrackMenuID)
                    ->addMenu(OnAdminMenu::TrackMenuID + 2, 'All Tracks', helper()->getIcon('playlist', 'icon:admin'), route('tracks.index'), parent: OnAdminMenu::TrackMenuID)

                ->addMenu(OnAdminMenu::GenreMenuID, 'Genres', helper()->getIcon('archive', 'icon:admin'), route('genres.create'), parent: OnAdminMenu::MediaMenuID)
                    ->addMenu(OnAdminMenu::GenreMenuID + 1, 'New Genre', helper()->getIcon('plus', 'icon:admin'), route('genres.create'), parent: OnAdminMenu::GenreMenuID)
                    ->addMenu(OnAdminMenu::GenreMenuID + 2, 'All Genres', helper()->getIcon('archive', 'icon:admin'), route('genres.index'), parent: OnAdminMenu::GenreMenuID)

                ->addMenu(OnAdminMenu::ArtistMenuID, 'Artist', helper()->getIcon('user-solid-circle', 'icon:admin'), route('artists.create'), parent:  OnAdminMenu::MediaMenuID)
                    ->addMenu(OnAdminMenu::ArtistMenuID + 1, 'All Artist', helper()->getIcon('users', 'icon:admin'), route('artists.index'), parent: OnAdminMenu::ArtistMenuID)
                    ->addMenu(OnAdminMenu::ArtistMenuID + 2, 'New Artist', helper()->getIcon('plus', 'icon:admin'), route('artists.create'), parent: OnAdminMenu::ArtistMenuID)

            ->addMenu(OnAdminMenu::FileManagerMenuID, 'File Manager', helper()->getIcon('media-file', 'icon:admin'), route('media.show'), parent:  OnAdminMenu::MediaMenuID);

        });
    }
}