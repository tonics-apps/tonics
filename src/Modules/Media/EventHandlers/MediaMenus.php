<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Media\EventHandlers;

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
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_MEDIA, $event->userRole()), function ($event) {
            return $event->addMenu(OnAdminMenu::MediaMenuID, 'Media', helper()->getIcon('play', 'icon:admin'), '#0')
            ->addMenu(OnAdminMenu::FileManagerMenuID, 'File Manager', helper()->getIcon('media-file', 'icon:admin'), route('media.show'), parent:  OnAdminMenu::MediaMenuID);
        });
    }
}