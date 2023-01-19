<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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
        $event->if(UserData::canAccess(Roles::getPermission(Roles::CAN_ACCESS_MEDIA), $event->userRole()), function ($event) {
            return $event->addMenu(OnAdminMenu::MediaMenuID, 'Media', helper()->getIcon('play', 'icon:admin'), '#0')
            ->addMenu(OnAdminMenu::FileManagerMenuID, 'File Manager', helper()->getIcon('media-file', 'icon:admin'), route('media.show'), parent:  OnAdminMenu::MediaMenuID);
        });
    }
}