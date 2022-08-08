<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\Rules;

use App\Modules\Core\Library\Tables;

trait PostValidationRules
{

    /**
     * @throws \Exception
     */
    public function postCategoryStoreRule(): array
    {
        $catSlugUnique = Tables::getTable(Tables::CATEGORIES) .':cat_slug';
        return [
            'cat_name' => ['required', 'string'],
            'cat_content' => ['string'],
            'cat_slug' => ['required', 'string', 'unique' => [
                $catSlugUnique => input()->fromPost()->retrieve('cat_slug', '')]
            ],
            'created_at' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function postCategoryUpdateRule(): array
    {
        $catSlugUnique = Tables::getTable(Tables::CATEGORIES) .':cat_slug:cat_id';
        return [
            'cat_name' => ['required', 'string'],
            'cat_content' => ['string'],
            'cat_slug' => ['required', 'string', 'unique' => [
                $catSlugUnique => input()->fromPost()->retrieve('cat_id', '')]
            ],
            'created_at' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function postStoreRule(): array
    {
        $postSlugUnique = Tables::getTable(Tables::POSTS) .':post_slug';
        return [
            'post_title' => ['required', 'string'],
            'post_slug' => ['required', 'string', 'unique' => [
                    $postSlugUnique => input()->fromPost()->retrieve('post_slug', '')]
            ],
            'post_content' => ['string'],
            'post_status' => ['required', 'numeric'],
            'user_id' => ['required', 'numeric'],
            'created_at' => ['required', 'string'],
            'image_url' => ['string'],
            'fk_cat_id' => ['numeric'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function postUpdateRule(): array
    {
        $postSlugUnique = Tables::getTable(Tables::POSTS) .':post_slug:post_id';
        return [
            'post_title' => ['required', 'string'],
            'post_slug' => ['required', 'string',
                'unique' => [
                    $postSlugUnique => input()->fromPost()->retrieve('post_id', '')]
            ],
            'post_content' => ['string'],
            'post_status' => ['required', 'numeric'],
            'user_id' => ['required', 'numeric'],
            'created_at' => ['required', 'string'],
            'image_url' => ['string'],
            'fk_cat_id' => ['numeric'],
        ];
    }

}