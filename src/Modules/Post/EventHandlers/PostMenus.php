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
use App\Modules\Core\Library\AdminMenuPaths;
use App\Modules\Core\Library\Authentication\Roles;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTreeSystem\Tree;

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
     * @throws \Throwable
     */
    public function handleEvent(object $event): void
    {
        tree()->group('', function (Tree $tree){

            $tree->add(AdminMenuPaths::POST, [
                'mt_name' => 'Blog',
                'mt_url_slug' => route('posts.index'),
                'mt_icon' => helper()->getIcon('note', 'icon:admin')
            ]);

            $tree->add(AdminMenuPaths::POST_NEW, [
                'mt_name' => 'New Post',
                'mt_url_slug' => route('posts.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(AdminMenuPaths::POST_CATEGORY_NEW, [
                'mt_name' => 'New Category',
                'mt_url_slug' => route('posts.category.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(AdminMenuPaths::POST_CATEGORY_ALL, [
                'mt_name' => 'All Categories',
                'mt_url_slug' => route('posts.category.index'),
                'mt_icon' => helper()->getIcon('category', 'icon:admin')
            ]);

        },['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_POST])], AdminMenuPaths::PRIORITY_MEDIUM);
    }
}
