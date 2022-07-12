<?php

namespace App\Themes\NinetySeven\Controller;

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

class PostsController
{
    private PostData $postData;
    private UserData $userData;
    private WidgetData $widgetData;

    public function __construct(PostData $postData, UserData $userData, WidgetData $widgetData)
    {
        $this->postData = $postData;
        $this->userData = $userData;
        $this->widgetData = $widgetData;
    }

    /**
     * @throws \Exception
     */
    public function singlePage($slugUniqueID, $slugString)
    {
        $post = $this->getPostData()->singlePost($slugUniqueID);

        if (property_exists('post_status', $post)) {
            if ($post->post_status === 1 && $post->cat_status === 1) {
                $this->showPost($post);
            }
            ## Else, post is in draft, check if user is logged in and has a read access
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
        addToGlobalVariable('Assets', ['css' => AppConfig::getThemesAsset('NinetySeven', 'css/styles.css')]);
        $post = [...json_decode($post->field_settings, true), ...(array)$post];
        $onFieldUserForm = new OnFieldUserForm([], new FieldData());

        $date = new DateTime($post['created_at']);
        $created_at_words = strtoupper($date->format('j M, Y'));
        $post['created_at_words'] = $created_at_words;

        renderBaseTemplate(CacheKeys::getSinglePostTemplateKey(), cacheNotFound: function () use ($onFieldUserForm, $post) {
            $fieldSlugs = $this->getFieldSlug($post);
            $onFieldUserForm->handleFrontEnd($fieldSlugs, $post);
            $this->saveTemplateCache();
        }, cacheFound: function () use ($onFieldUserForm, $post) {
            # quick check if single template parts have not been cached...if not we force parse it...
            if (!isset(getBaseTemplate()->getModeStorage('add_hook')['site_credits'])) {
                $fieldSlugs = $this->getFieldSlug($post);
                $onFieldUserForm->handleFrontEnd($fieldSlugs, $post);
                // re-save cache data
                $this->saveTemplateCache();
            }

            getBaseTemplate()->addToVariableData('Data', $post);
        });
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
     * @throws \Exception
     */
    public function saveTemplateCache(): void
    {
        getBaseTemplate()->removeVariableData('BASE_TEMPLATE');
        apcu_store(CacheKeys::getSinglePostTemplateKey(), [
            'contents' => getBaseTemplate()->getContent(),
            'modeStorage' => getBaseTemplate()->getModeStorages(),
            'variable' => getBaseTemplate()->getVariableData()
        ]);
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
     * @return WidgetData
     */
    public function getWidgetData(): WidgetData
    {
        return $this->widgetData;
    }
}