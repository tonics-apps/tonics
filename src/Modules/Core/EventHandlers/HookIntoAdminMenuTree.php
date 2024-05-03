<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Events\OnAdminMenu;
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Menu\Data\MenuData;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class HookIntoAdminMenuTree implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        if (AppConfig::TonicsIsReady() === false) {
            return;
        }

        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::before_in_main_header_title', function (TonicsView $tonicsView){
            $foundNode = request()->getRouteObject()->getRouteTreeGenerator()?->getFoundURLNode();
            $path = $foundNode->getFullRoutePath();

            $findURL = request()->getRouteObject()->getRouteTreeGenerator()->findURL($path);
            $breadCrumb = '';

            if (empty(tree()->getTreeGenerator()->getAnyData())){
                AppConfig::initAdminMenu(false);
            }

            if (isset(tree()->getTreeGenerator()->getAnyData()['BreadCrumbMapper'][$path])){
                $urlNodePath = tree()->getTreeGenerator()->getAnyData()['BreadCrumbMapper'][$path];
                $node = tree()->getTreeGenerator()->findURL($urlNodePath);
                $frag = '';
                foreach ($node->getParentRecursive() as $nodeP){

                    if (!isset($nodeP->getSettings()['settings']['mt_url_slug'])){
                        continue;
                    }

                    $url = $nodeP->getSettings()['settings']['mt_url_slug'];
                    $name = $nodeP->getSettings()['settings']['mt_name'];

                    if (isset($nodeP->getSettings()['settings']['route'])){
                        $url = route('tonicsCloud.containers.apps.index', $findURL->getFoundURLRequiredParams());
                    }
                    $frag = <<<FRAG
            <li class="tonics-breadcrumb-item">
                <a href="$url" class="box color:black border-width:default border:black text-underline button:box-shadow-variant-2" title="$name">
                    <div class="text:no-wrap">$name</div>
                </a>
            </li>
FRAG . $frag;

                    if (!isset($nodeP->getSettings()['settings']['home'])){
                        $frag = <<<FRAG
           <li class="tonics-breadcrumb-item">
                <div class="box d:flex color:black border-width:default border:black button:box-shadow-variant-2" title="is a parent of »">
                    <div class="text:no-wrap">/</div>
                </div>
            </li>
FRAG . $frag;
                    }
                }

                $breadCrumb = <<<HTML
<ol style="gap: 0.5em;padding-left: 10px; justify-content: flex-start; align-items: center; font-size: 80%; margin-top:5px;" class="tonics-breadcrumb d:flex flex-wrap:wrap justify-content:center list:style:none">
<style>
.box {
    display: flex;
    flex-direction: row;
    padding: 5px clamp(15px, 2vw, 20px);
    align-items: center;
    border-radius: 5px;
    text-decoration: none;
}
</style>
$frag
</ol>
HTML;
            }

            return $breadCrumb;
        });

        $event->hookInto('Core::after_admin_menu_tree', function (TonicsView $tonicsView){
            try {

                $menuData = new MenuData();
                $menuHTMLFRag = $menuData->generateMenuTree();

                # For Admin User
                if (UserData::canAccess(Roles::CAN_ACCESS_CORE, UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role))){
                    $adminCacheClearRoute = route('admin.cache.clear');
                    if (!empty($adminCacheClearRoute)){
                        $adminCacheClearRoute = $adminCacheClearRoute . "?token=" . AppConfig::getAppKey();
                    }
                    $token = session()->getCSRFToken();
                    $logout = route('admin.logout');
                    return <<<HTML
$menuHTMLFRag
<li class="menu-block" data-menu-depth="0">
            <a href="" class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black" title="">
                <svg class="icon:admin tonics-cog"> <use xlink:href="#tonics-cog"></use></svg>
                <div class="text:paragraph-fluid-one text:no-wrap">Settings</div>
                <button class="dropdown-toggle bg:transparent  border:none" aria-expanded="false" aria-label="Expand child menu" data-menutoggle_click_outside="true">
                    <svg class="icon:admin tonics-arrow-down">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </a>
            <!-- The child menu-->
            <ul class="child-menu z-index:child-menu site-navigation-ul flex-gap d:none list:style:none">
<!--                <li class="menu-block" data-menu-depth="1">
                    <a href="" class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black" title="">
                        <svg class="icon:admin tonics-cog"> <use xlink:href="#tonics-cog"></use></svg>
                        <div class="text:paragraph-fluid-one text:no-wrap">General</div>
                    </a>
                </li>-->
                <li class="menu-block" data-menu-depth="1">
                    <form method="post" class="d:flex height:100%" action="$logout">
                        <input type="hidden" name="token" value="$token">
                        <button class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black cursor:pointer" title="">
                            <svg class="icon:admin dashboard"> <use xlink:href="#tonics-dashboard"></use></svg>
                            <span class="text:paragraph-fluid-one text:no-wrap">Logout</span>
                        </button>
                    </form>
                </li>
                <li class="menu-block" data-menu-depth="1">
                    <a href="$adminCacheClearRoute" class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black" title="">
                        <svg class="icon:admin dashboard"> <use xlink:href="#tonics-trash-can"></use></svg>
                        <div class="text:paragraph-fluid-one text:no-wrap">Clear Cache</div>
                    </a>
                </li>
            </ul>
        </li>
HTML;
                }

                # For Customer User
                if (UserData::canAccess(Roles::CAN_ACCESS_CUSTOMER, UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role))){
                    $token = session()->getCSRFToken();
                    $logout = route('customer.logout');
                    return <<<HTML
$menuHTMLFRag
<li class="menu-block" data-menu-depth="0">
            <a href="" class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black" title="">
                <svg class="icon:admin tonics-cog"> <use xlink:href="#tonics-cog"></use></svg>
                <div class="text:paragraph-fluid-one text:no-wrap">Settings</div>
                <button class="dropdown-toggle bg:transparent  border:none" aria-expanded="false" aria-label="Expand child menu" data-menutoggle_click_outside="true">
                    <svg class="icon:admin tonics-arrow-down">
                        <use class="svgUse" xlink:href="#tonics-arrow-down"></use>
                    </svg>
                </button>
            </a>
            <!-- The child menu-->
            <ul class="child-menu z-index:child-menu site-navigation-ul flex-gap d:none list:style:none">
<!--                <li class="menu-block" data-menu-depth="1">
                    <a href="" class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black" title="">
                        <svg class="icon:admin tonics-cog"> <use xlink:href="#tonics-cog"></use></svg>
                        <div class="text:paragraph-fluid-one text:no-wrap">Profile</div>
                    </a>
                </li>-->
                <li class="menu-block" data-menu-depth="1">
                    <form method="post" class="d:flex height:100%" action="$logout">
                        <input type="hidden" name="token" value="$token">
                        <button class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black cursor:pointer" title="">
                            <svg class="icon:admin dashboard"> <use xlink:href="#tonics-dashboard"></use></svg>
                            <span class="text:paragraph-fluid-one text:no-wrap">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </li>
HTML;
                }
            } catch (\Exception $exception){
                // Log..
            }
            return '';
        });
    }
}