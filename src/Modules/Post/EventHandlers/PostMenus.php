<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\EventHandlers;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Library\Authentication\Roles;
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
