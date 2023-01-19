<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Menu\EventHandlers;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class MenuMenus implements HandlerInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::getPermission(Roles::CAN_ACCESS_MENU), $event->userRole()), function ($event) {
            return $event->addMenu(OnAdminMenu::MenusMenuID, 'Menu', helper()->getIcon('menu', 'icon:admin'), route('menus.create'), parent:  OnAdminMenu::ToolsMenuID)
                ->addMenu(OnAdminMenu::MenusMenuID + 1, 'New Menu', helper()->getIcon('plus', 'icon:admin'), route('menus.create'), parent: OnAdminMenu::MenusMenuID)
                ->addMenu(OnAdminMenu::MenusMenuID + 2, 'All Menus', helper()->getIcon('notes', 'icon:admin'), route('menus.index'), parent: OnAdminMenu::MenusMenuID);
        });
    }
}