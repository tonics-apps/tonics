<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
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