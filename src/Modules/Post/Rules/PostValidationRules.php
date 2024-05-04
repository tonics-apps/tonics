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
    public function postCategoryUpdateMultipleRule(): array
    {
        return [
            'cat_id' => ['numeric'],
            'cat_name' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
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
            'fk_cat_id' => ['array'],
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
            'fk_cat_id' => ['required', 'array'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function postUpdateMultipleRule(): array
    {
        return [
            'post_id' => ['numeric'],
            'post_title' => ['required', 'string'],
            'updated_at' => ['required', 'string'],
            'fk_cat_id' => ['required', 'array'],
        ];
    }

}