<?php

namespace App\Themes\NinetySeven\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Field\Data\FieldData;
use App\Modules\Field\Events\OnFieldUserForm;
use App\Modules\Post\Data\PostData;
use App\Modules\Widget\Data\WidgetData;
use DateTime;

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

        if (isset($post->post_status) && $post->post_status === 1 && $post->cat_status === 1) {
            $this->showPost($post);
            exit();
        }

        ## If Post is in draft, check if user is logged in and has a read access
        if (isset($post->post_status) && $post->post_status === 0) {
            $role = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role);
            if (Roles::RoleHasPermission($role, Roles::CAN_READ)) {
                $this->showPost($post);
                exit();
            }
        }

        SimpleState::displayUnauthorizedErrorMessage(SimpleState::ERROR_PAGE_NOT_FOUND__CODE, SimpleState::ERROR_PAGE_NOT_FOUND__MESSAGE);
    }

    /**
     * @throws \Exception
     */
    private function showPost($post)
    {
        $post = [...json_decode($post->field_settings, true), ...(array)$post];
        $foundURL = url()->getRouteObject()->getRouteTreeGenerator()->getFoundURLNode();
        $postMoreSettings = $foundURL->getMoreSettings('GET');
        $onFieldUserForm = new OnFieldUserForm([], new FieldData());

        $MenuWidgetsSidebarTitle = null;
        $MenuWidgetsSidebarData = $this->getWidgetData()->getWidgetViewListing(
            $this->getWidgetData()->getWidgetLocationItems(onBeforeDecodingWidget: function ($data) use (&$MenuWidgetsSidebarTitle) {
                if (isset($data[0]->widget_name)) {
                    $MenuWidgetsSidebarTitle = $data[0]->widget_name;
                }
            }));
        dd($post);

        $date = new DateTime($post['created_at']);
        $created_at_words = strtoupper($date->format('j M, Y'));
        $post['created_at_words'] = $created_at_words;

        view('Themes::NinetySeven/Views/Post/single', [
            'SiteURL' => AppConfig::getAppUrl(),
            'Data' => $post,
            'Assets' => [
                'css' => AppConfig::getThemesAsset('NinetySeven', 'css/styles.css')
            ],
            'MenuWidgetsSidebar' => [
                'Title' => $MenuWidgetsSidebarTitle,
                'Data' => $MenuWidgetsSidebarData,
            ],
            'TimeZone' => AppConfig::getTimeZone(),
            'FieldItems' => $onFieldUserForm->generateHTMLFrags($postMoreSettings, $post, true),
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