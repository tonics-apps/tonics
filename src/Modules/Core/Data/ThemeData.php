<?php

namespace App\Modules\Core\Data;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;

class ThemeData extends AbstractDataLayer
{

    /**
     * @throws \Exception
     */
    public function adminThemeListing($themes): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = '';
        $urlPrefix = "/admin/tools/themes";
        $k = 0;
        foreach ($themes as $themeKey => $themeInstance) {
            if ($themeInstance instanceof PluginConfig) {
                $themeDirName = helper()->getFileName(helper()->getClassDirectory($themeInstance));
                $themePath = AppConfig::getThemesPath() . DIRECTORY_SEPARATOR . $themeDirName;
                $info_url = isset($themeInstance->info()['info_url']) ? $themeInstance->info()['info_url'] : '';
                $description = isset($themeInstance->info()['description']) ? $themeInstance->info()['description'] : '';
                $themeName = isset($themeInstance->info()['name']) ? $themeInstance->info()['name'] : $themeDirName;

                $installOrActivateLink = <<<HTML
<a href="$urlPrefix/$themeName/install" 
class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer">Install</a>
HTML;
                if (helper()->fileExists($themePath . DIRECTORY_SEPARATOR . '.installed')) {
                    $installOrActivateLink = <<<HTML
<a href="$urlPrefix/$themeName/uninstall" 
class="listing-button text-align:center bg:transparent border:none color:black bg:white-one border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer">Uninstall</a>
HTML;
                }


                $htmlFrag .= <<<HTML
    <li 
    data-list_id="$k"
    data-theme_key="$themeKey"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$themeDirName</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$themeName</div>
                <div class="form-group d:flex flex-gap:small">
                     $installOrActivateLink  
                   <form method="post" class="d:contents" action="$urlPrefix/$themeKey/delete">
                    <input type="hidden" name="token" value="$csrfToken" >
                       <button data-click-onconfirmdelete="true" type="button" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:gentle
                        margin-top:0 cart-width cursor:pointer">Delete</button>
                    </form>
                </div>
                
                <ul class="more-info list:style:none d:flex flex-gap justify-content:center">
                    <li class="menu-block" data-menu-depth="0">
                        <a href="$info_url" target="_blank" class="extension-box flex-gap:small color:black border-width:default border:black" title="">
                            <div class="text:paragraph-fluid-one text:no-wrap">Info</div>        
                        </a>
                    </li>
                    <li class="menu-block" data-menu-depth="0">
                        <a href="#0" class="extension-box flex-gap:small color:black border-width:default border:black" title="">
                            <div class="text:paragraph-fluid-one text:no-wrap">Update</div>        
                        </a>
                    </li>
                </ul>
            </div>
        </fieldset>
    </li>
HTML;

            }
            ++$k;
        }

        return $htmlFrag;
    }

    /**
     * @throws \Exception
     */
    public function getThemeObject(string $themeName)
    {
        $themeFullClass = "App\Themes\\$themeName\\{$themeName}Activator";
        $implements = class_implements($themeFullClass);
        if (is_array($implements) && key_exists(ModuleConfig::class, $implements)) {
            return new $themeFullClass;
        }

        return null;
    }
}