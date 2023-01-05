<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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