<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\CacheKeys;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldUserForm;
use App\Modules\Post\Data\PostData;
use App\Modules\Widget\Data\WidgetData;
use DateTime;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use JetBrains\PhpStorm\NoReturn;

class PostsController
{
    private PostData $postData;
    private UserData $userData;
    private FieldData $fieldData;

    /**
     * @param PostData $postData
     * @param UserData $userData
     * @param FieldData $fieldData
     * @throws \Exception
     */
    public function __construct(PostData $postData, UserData $userData, FieldData $fieldData)
    {
        $this->postData = $postData;
        $this->userData = $userData;
        $this->fieldData = $fieldData;
        addToGlobalVariable('Assets', ['css' => AppConfig::getAppAsset('NinetySeven', 'css/styles.min.css')]);
    }

    /**
     * @throws \Exception
     */
    public function singlePost($slugUniqueID, $slugString)
    {
        $post = $this->getPostData()->getPostByUniqueID($slugUniqueID);
        if (is_array($post) && key_exists('post_status', $post)) {
            if ($post['post_status'] === 1 && $post['cat_status'] === 1) {
                $this->showPost($post);
            }

            ## Else, post is in draft or trash, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->showPost($post);
            }
        }

        SimpleState::displayUnauthorizedErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
    }

    /**
     * @throws \Exception
     */
    private function showPost($post)
    {
        $this->fieldData->unwrapForPost($post);
        $onFieldUserForm = new OnFieldUserForm([], $this->fieldData);

        renderBaseTemplate(CacheKeys::getSinglePostTemplateKey(), cacheNotFound: function () use ($onFieldUserForm, $post) {
            $fieldSlugs = $this->getFieldSlug($post);
            $onFieldUserForm->handleFrontEnd($fieldSlugs, $post);
            cacheBaseTemplate();
        }, cacheFound: function () use ($onFieldUserForm, $post) {
            # quick check if single template parts have not been cached...if not we force parse it...
            if (!isset(getBaseTemplate()->getModeStorage('add_hook')['site_credits'])) {
                $fieldSlugs = $this->getFieldSlug($post);
                $onFieldUserForm->handleFrontEnd($fieldSlugs, $post);
                // re-save cache data
               cacheBaseTemplate();
            }

            getBaseTemplate()->addToVariableData('Data', $post);
        });
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function singleCategory($slugUniqueID, $slugString)
    {
        $category = $this->getPostData()->selectWithConditionFromCategory(['*'], "slug_id = ?", [$slugUniqueID]);
        if (is_object($category) && property_exists($category, 'cat_status')) {
            if ($category->cat_status === 1) {
                $this->showCategory($category);
            }
            ## Else, category is in draft, check if user is logged in and has a read access
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->showCategory($category);
            }
        }

        SimpleState::displayUnauthorizedErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
    }

    /**
     * @throws \Exception
     */
    private function showCategory($category)
    {
        $category = (array)$category;
        $date = new DateTime($category['created_at']);
        $category['created_at_words'] = strtoupper($date->format('j M, Y'));
        $onFieldUserForm = new OnFieldUserForm([], new FieldData());

        // category doesn't have the concepts of fields, but we could still use hard-coded field
        $fieldSlugs = ['single-category-view'];
        renderBaseTemplate(CacheKeys::getSinglePostTemplateKey(), cacheNotFound: function () use ($fieldSlugs, $onFieldUserForm, $category) {
            $onFieldUserForm->handleFrontEnd($fieldSlugs, $category);
            cacheBaseTemplate();
        }, cacheFound: function () use ($onFieldUserForm, $category) {
            # quick check if single template parts have not been cached...if not we force parse it...
            if (!isset(getBaseTemplate()->getModeStorage('add_hook')['site_credits'])) {
                $fieldSlugs = $this->getFieldSlug($category);
                $onFieldUserForm->handleFrontEnd($fieldSlugs, $category);
                // re-save cache data
                cacheBaseTemplate();
            }

            getBaseTemplate()->addToVariableData('Data', $category);
        });
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
     * @return UserData
     */
    public function getUserData(): UserData
    {
        return $this->userData;
    }

    /**
     * @return FieldData
     */
    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

}