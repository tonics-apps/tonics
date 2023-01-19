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
use App\Modules\Core\Events\TonicsTemplateViewEvent\Hook\OnHookIntoTemplate;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;
use Devsrealm\TonicsTemplateSystem\TonicsView;

class HookIntoAdminMenuTree implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnHookIntoTemplate */
        $event->hookInto('Core::after_admin_menu_tree', function (TonicsView $tonicsView){
            try {
                if (UserData::canAccess(Roles::getPermission(Roles::CAN_ACCESS_CORE), UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role))){
                    $adminCacheClearRoute = route('admin.cache.clear');
                    if (!empty($adminCacheClearRoute)){
                        $adminCacheClearRoute = $adminCacheClearRoute . "?token=" . AppConfig::getAppKey();
                    }
                    $token = session()->getCSRFToken();
                    $logout = route('admin.logout');
                    return <<<HTML
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
                <li class="menu-block" data-menu-depth="1">
                    <a href="" class="menu-box flex-gap:small color:black bg:white-one border-width:default border:black" title="">
                        <svg class="icon:admin tonics-cog"> <use xlink:href="#tonics-cog"></use></svg>
                        <div class="text:paragraph-fluid-one text:no-wrap">General</div>
                    </a>
                </li>
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
            } catch (\Exception $exception){
                // Log..
            }
            return '';
        });
    }
}