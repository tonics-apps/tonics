<?php
/*
 *     Copyright (c) 2025. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Post\Controllers;

use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Post\Services\PostService;

class PostControllerAPI
{
    public function __construct(private PostService $postService)
    {
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function QueryPost(): void
    {
        try {
            $body = url()->getEntityBody();
            $body = json_decode($body, true) ?? [];
            $post = $this->postService::QueryLoop($body);
            helper()->onSuccess($post);
        } catch (\Exception $e) {
            helper()->onError(500, $e->getMessage());
        }
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function QueryPostCategory(): void
    {
        try {
            $body = url()->getEntityBody();
            $body = json_decode($body, true) ?? [];
            $postCategory = $this->postService::QueryLoopCategory($body);
            helper()->onSuccess($postCategory);
        } catch (\Exception $e) {
            helper()->onError(500, $e->getMessage());
        }
    }

    /**
     * @throws \Throwable
     */
    public function PostPageLayout(string $slugID): void
    {
        $post = PostService::QueryLoop([
            PostService::QUERY_LOOP_SETTINGS_POST_IN => " id='$slugID' ",
        ]);

        if (isset($post->data[0])) {
            $post = $post->data[0];
            if (isset($post->field_settings)) {
                helper()->onSuccess(FieldConfig::quickProcessLogicFieldDetails($post, 'post_content'));
            }
        }

        helper()->onError(500, 'Invalid Post SlugID');
    }

    /**
     * @param string $slugID
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function PostCategoryPageLayout(string $slugID): void
    {
        $postCategory = PostService::QueryLoopCategory([
            PostService::QUERY_LOOP_SETTINGS_CATEGORY_FIELD_NAME => 'slug_id',
            PostService::QUERY_LOOP_SETTINGS_CATEGORY_IN => $slugID,
        ]);

        if (isset($postCategory->data[0])) {
            $postCategory = $postCategory->data[0];
            if (isset($postCategory->field_settings)) {
                helper()->onSuccess(FieldConfig::quickProcessLogicFieldDetails($postCategory, 'cat_content'));
            }
        }

        helper()->onError(500, 'Invalid PostCategory SlugID');
    }
}