<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page\EventHandlers;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PageMenu implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_PAGE, $event->userRole()), function ($event) {
            return $event->addMenu(OnAdminMenu::PageMenuID, 'Pages', helper()->getIcon('archive', 'icon:admin'), '#0')
                ->addMenu(OnAdminMenu::PageMenuID + 1, 'New Page', helper()->getIcon('plus', 'icon:admin'), route('pages.create'), parent: OnAdminMenu::PageMenuID)
                ->addMenu(OnAdminMenu::PageMenuID + 2, 'All Pages', helper()->getIcon('archive', 'icon:admin'), route('pages.index'), parent: OnAdminMenu::PageMenuID);
        });
    }
}