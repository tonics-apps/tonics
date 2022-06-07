<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Post\EventHandlers;

use App\Library\Authentication\Roles;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;

use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

/**
 * This Listens to the OnAdminMenu and Whenever the event fires, we call this listener
 *
 * The purpose of this listener is to add post menu functionality, such as:
 * showing post menu, adding new post menu, category menu, and anything related to post
 * Class DefaultTemplate
 * @package Modules\Core\EventHandlers
 */
class PostMenus implements HandlerInterface
{

    /**
     * @param object $event
     * @throws \Exception
     */
    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
        $event->if(UserData::canAccess(Roles::CAN_ACCESS_POST, $event->userRole()), function ($event) {
            return $event->addMenu(OnAdminMenu::BlogMenuID, 'Blog', helper()->getIcon('note', 'icon:admin'), route('posts.create'))
                ->addMenu(OnAdminMenu::BlogMenuID + 1, 'New Post', helper()->getIcon('plus', 'icon:admin'), route('posts.create'), parent: OnAdminMenu::BlogMenuID)
                ->addMenu(OnAdminMenu::BlogMenuID + 2, 'All Posts', helper()->getIcon('notes', 'icon:admin'), route('posts.index'), parent: OnAdminMenu::BlogMenuID)
                ->addMenu(OnAdminMenu::BlogMenuID + 3, 'New Category', helper()->getIcon('plus', 'icon:admin'), route('posts.category.create'), parent: OnAdminMenu::BlogMenuID)
                ->addMenu(OnAdminMenu::BlogMenuID + 4, 'All Categories', helper()->getIcon('category', 'icon:admin'), route('posts.category.index'), parent: OnAdminMenu::BlogMenuID);
        });
    }
}
