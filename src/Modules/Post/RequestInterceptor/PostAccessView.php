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
use App\Modules\Page\Events\OnPageDefaultField;
use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnPostDefaultField;
use JetBrains\PhpStorm\NoReturn;

class PostAccessView
{
    private PostData $postData;
    private array $post = [];
    private array $category = [];

    public function __construct(PostData $postData){
        $this->postData = $postData;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function handlePost(): void
    {
        $ID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $post = (array)$this->getPostData()->getPostByUniqueID($ID);

        # if empty we can check with the post_slug
        if (empty($post)){
            $ID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[1] ?? null;
            $post = (array)$this->getPostData()->getPostByUniqueID($ID, 'post_slug');
        }

        if (key_exists('post_status', $post)) {
            $postCreatedAtTimeStamp = strtotime($post['published_time']);
            if ($post['post_status'] === 1 && $post['cat_status'] === 1 && time() >= $postCreatedAtTimeStamp) {
                $this->post = $post; return;
            }

            ## Else, post is in draft or trash or in the future, check if user is logged in and has a read access
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
        $ID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[0] ?? null;
        $category = (array)$this->getPostData()->selectWithConditionFromCategory(['*'], "slug_id = ?", [$ID]);

        # if empty we can check with the cat_slug
        if (empty($category)){
            $ID = request()->getRouteObject()->getRouteTreeGenerator()->getFoundURLRequiredParams()[1] ?? null;
            $category = (array)$this->getPostData()->selectWithConditionFromCategory(['*'], "cat_slug = ?", [$ID]);
        }


        if (key_exists('cat_status', $category)) {
            $catCreatedAtTimeStamp = strtotime($category['created_at']);
            if ($category['cat_status'] === 1 && time() >= $catCreatedAtTimeStamp) {
                $this->category = $category; return;
            }
            ## Else, category is in draft, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->category = $category; return;
            }
        }

        SimpleState::displayUnauthorizedErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function showPost(string $postView, $moreData = []): void
    {
        $post = $this->post;
        if (!empty($post)){
            $this->getFieldData()->unwrapForPost($post);
            $onFieldUserForm = new OnFieldFormHelper([], $this->getFieldData());

            event()->dispatch($this->getPostData()->getOnPostDefaultField());

            # We are only interested in the hidden slug
            $slugs = $this->getPostData()->getOnPostDefaultField()->getHiddenFieldSlug();
            # Cache Post Data
            $onFieldUserForm->handleFrontEnd($slugs, [...$post, ...$moreData]);
            view($postView);
        }

        exit();
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function showCategory(string $postView, $moreData = []): void
    {
        $category = $this->category;
        if (!empty($category)){
            $fieldSettings = json_decode($category['field_settings'], true);
            $this->getFieldData()->unwrapFieldContent($fieldSettings, contentKey: 'cat_content');
            $category = [...$fieldSettings, ...$category];

            $date = new \DateTime($category['created_at']);
            $category['created_at_words'] = strtoupper($date->format('j M, Y'));
            $onFieldUserForm = new OnFieldFormHelper([], $this->getFieldData());

            event()->dispatch($this->getPostData()->getOnPostCategoryDefaultField());
            $slugs = event()->dispatch(new OnPostDefaultField())->getHiddenFieldSlug();
            $onFieldUserForm->handleFrontEnd($slugs, [...$category, ...$moreData]);
            view($postView);
        }

        exit();
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
        return $this->getPostData()->getFieldData();
    }
}