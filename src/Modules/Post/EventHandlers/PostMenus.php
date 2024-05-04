<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Post\EventHandlers;

use App\Modules\Core\Library\AdminMenuHelper;
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

            $tree->add(AdminMenuHelper::POST, [
                'mt_name' => 'Blog',
                'mt_url_slug' => route('posts.index'),
                'mt_icon' => helper()->getIcon('note', 'icon:admin')
            ]);

            $tree->add(AdminMenuHelper::POST_NEW, [
                'mt_name' => 'New Post',
                'mt_url_slug' => route('posts.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(AdminMenuHelper::POST_EDIT, [
                'mt_name' => 'Edit Post',
                'mt_url_slug' => '/admin/posts/:post/edit',
                'ignore' => true,
            ]);

            $tree->add(AdminMenuHelper::POST_CATEGORY_NEW, [
                'mt_name' => 'New Category',
                'mt_url_slug' => route('posts.category.create'),
                'mt_icon' => helper()->getIcon('plus', 'icon:admin')
            ]);

            $tree->add(AdminMenuHelper::POST_CATEGORY_ALL, [
                'mt_name' => 'All Categories',
                'mt_url_slug' => route('posts.category.index'),
                'mt_icon' => helper()->getIcon('category', 'icon:admin')
            ]);

            $tree->add(AdminMenuHelper::POST_CATEGORY_EDIT, [
                'mt_name' => 'Edit Category',
                'mt_url_slug' => '/admin/posts/category/:category/edit',
                'ignore' => true,
            ]);

        },['permission' => Roles::GET_PERMISSIONS_ID([Roles::CAN_ACCESS_POST])], AdminMenuHelper::PRIORITY_MEDIUM);
    }
}
