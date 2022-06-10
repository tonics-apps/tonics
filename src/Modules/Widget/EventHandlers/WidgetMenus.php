<?php

namespace App\Modules\Widget\EventHandlers;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class WidgetMenus implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_WIDGET, $event->userRole()), function ($event) {
            return $event->addMenu(OnAdminMenu::WidgetsMenuID, 'Widget', helper()->getIcon('widget', 'icon:admin'), route('widgets.create'), parent:  OnAdminMenu::ToolsMenuID)
                ->addMenu(OnAdminMenu::WidgetsMenuID + 1, 'New Widget', helper()->getIcon('plus', 'icon:admin'), route('widgets.create'), parent: OnAdminMenu::WidgetsMenuID)
                ->addMenu(OnAdminMenu::WidgetsMenuID + 2, 'All Widgets', helper()->getIcon('notes', 'icon:admin'), route('widgets.index'), parent: OnAdminMenu::WidgetsMenuID);
        });
    }
}