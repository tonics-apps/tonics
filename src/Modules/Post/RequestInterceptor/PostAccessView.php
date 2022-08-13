<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\RequestInterceptor;

use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldFormHelper;
use App\Modules\Post\Data\PostData;

class PostAccessView
{
    private PostData $postData;
    private FieldData $fieldData;
    private array $post = [];
    private array $category = [];

    public function __construct(PostData $postData,  FieldData $fieldData){
        $this->postData = $postData;
        $this->fieldData = $fieldData;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handlePost(): void
    {
        $uniqueID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $post = $this->getPostData()->getPostByUniqueID($uniqueID);
        if (is_array($post) && key_exists('post_status', $post)) {
            if ($post['post_status'] === 1 && $post['cat_status'] === 1) {
                $this->post = $post; return;
            }

            ## Else, post is in draft or trash, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->post = $post; return;
            }
        }

        SimpleState::displayUnauthorizedErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
    }

    /**
     * @throws \Exception
     */
    public function handleCategory()
    {
        $uniqueID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $category = $this->getPostData()->selectWithConditionFromCategory(['*'], "slug_id = ?", [$uniqueID]);
        if (is_object($category) && property_exists($category, 'cat_status')) {
            if ($category->cat_status === 1) {
                $this->category = (array)$category; return;
            }
            ## Else, category is in draft, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->category = (array)$category; return;
            }
        }

        SimpleState::displayUnauthorizedErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
    }

    /**
     * @throws \Exception
     */
    public function showPost(string $postView)
    {
        $post = $this->post;
        if (!empty($post)){
            $this->fieldData->unwrapForPost($post);
            $onFieldUserForm = new OnFieldFormHelper([], $this->fieldData);
            # Cache Post Data
            $onFieldUserForm->handleFrontEnd($this->getFieldSlug($post), $post);
            view($postView);
        }

        exit();
    }

    /**
     * @throws \Exception
     */
    public function showCategory(string $postView)
    {

        $category = $this->category;
        if (!empty($category)){
            $date = new \DateTime($category['created_at']);
            $category['created_at_words'] = strtoupper($date->format('j M, Y'));
            $onFieldUserForm = new OnFieldFormHelper([], $this->fieldData);

            // category doesn't have the concepts of fields (not yet), but we could still use hard-coded field
            $fieldSlugs = ['single-category-view'];
            $onFieldUserForm->handleFrontEnd($fieldSlugs, $category);
            view($postView);
        }

        exit();
    }

    /**
     * @throws \Exception
     */
    public function getFieldSlug($post): array
    {
        $slug = $post['field_ids'];
        $fieldSlugs = json_decode($slug) ?? [];
        if (is_object($fieldSlugs)) {
            $fieldSlugs = (array)$fieldSlugs;
        }

        if (empty($fieldSlugs) || !is_array($fieldSlugs)) {
            // re-save default fields
            $default = ["post-page", "seo-settings"];
            $updatePost = ['field_ids' => json_encode($default)];
            $post['field_settings'] = (array)json_decode($post['field_settings']);
            if (empty($post['field_settings']['seo_title'])) {
                $post['field_settings']['seo_title'] = $post['post_title'];
            }
            if (empty($post['field_settings']['seo_description'])) {
                $post['field_settings']['seo_description'] = substr(strip_tags($post['post_content']), 0, 200);
            }
            $updatePost['field_settings'] = json_encode($post['field_settings']);
            $this->postData->updateWithCondition($updatePost, ['post_id' => (int)$post['post_id']], Tables::getTable(Tables::POSTS));
            return $default;
        }

        return $fieldSlugs;
    }

    /**
     * @return PostData
     */
    public function getPostData(): PostData
    {
        return $this->postData;
    }

    /**
     * @param PostData $postData
     */
    public function setPostData(PostData $postData): void
    {
        $this->postData = $postData;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData $fieldData
     */
    public function setFieldData(FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }
}