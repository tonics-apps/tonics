<?php

namespace App\Modules\Menu\EventHandlers;

use App\Library\Authentication\Roles;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
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
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_MENU, $event->userRole()), function ($event) {
            return $event->addMenu(OnAdminMenu::MenusMenuID, 'Menu', helper()->getIcon('menu', 'icon:admin'), route('menus.create'), parent:  OnAdminMenu::ToolsMenuID)
                ->addMenu(OnAdminMenu::MenusMenuID + 1, 'New Menu', helper()->getIcon('plus', 'icon:admin'), route('menus.create'), parent: OnAdminMenu::MenusMenuID)
                ->addMenu(OnAdminMenu::MenusMenuID + 2, 'All Menus', helper()->getIcon('notes', 'icon:admin'), route('menus.index'), parent: OnAdminMenu::MenusMenuID);
        });
    }
}