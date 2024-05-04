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

namespace App\Modules\Post\Helper;


class PostRedirection
{
    /**
     * @param array $post
     * @return string
     */
    public static function getPostAbsoluteURLPath(array $post): string
    {
        if (isset($post['slug_id']) && isset($post['post_slug'])){
            return "/posts/{$post['slug_id']}/{$post['post_slug']}";
        }

        return '';
    }

    public static function getCategoryAbsoluteURLPath(array $category): string
    {
        if (isset($category['slug_id']) && isset($category['cat_slug'])){
            return "/categories/{$category['slug_id']}/{$category['cat_slug']}";
        }

        return '';
    }
}