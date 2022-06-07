<?php

namespace App\Modules\Field\EventHandlers;

use App\Library\Authentication\Roles;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class FieldMenus implements HandlerInterface
{

    /**
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_FIELD, $event->userRole()), function ($event) {
            return $event->addMenu(OnAdminMenu::FieldMenuID, 'Field', helper()->getIcon('widget', 'icon:admin'), route('fields.create'), parent:  OnAdminMenu::ToolsMenuID)
                ->addMenu(OnAdminMenu::FieldMenuID + 1, 'New Field', helper()->getIcon('plus', 'icon:admin'), route('fields.create'), parent: OnAdminMenu::FieldMenuID)
                ->addMenu(OnAdminMenu::FieldMenuID + 2, 'All Fields', helper()->getIcon('notes', 'icon:admin'), route('fields.index'), parent: OnAdminMenu::FieldMenuID);
        });
    }
}