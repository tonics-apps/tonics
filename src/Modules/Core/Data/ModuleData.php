<?php

namespace App\Modules\Core\Data;

use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;

class ModuleData extends AbstractDataLayer
{
    /**
     * @throws \Exception
     */
    public function adminModuleListing($modules): string
    {
        $csrfToken = session()->getCSRFToken();
        $htmlFrag = ''; $urlPrefix = "/admin/tools/modules";
        $k = 0;
        foreach ($modules as $moduleKey => $moduleInstance){
            if ($moduleInstance instanceof PluginConfig){
                $moduleDirName =  helper()->getFileName(helper()->getClassDirectory($moduleInstance));
                $modulePath = AppConfig::getThemesPath() . DIRECTORY_SEPARATOR . $moduleDirName;
                $moduleName = isset($moduleInstance->info()['name']) ? $moduleInstance->info()['name'] : $moduleDirName;

                $htmlFrag .= <<<HTML
    <li 
    data-list_id="$k"
    data-theme_key="$moduleKey"
    tabindex="0" 
    class="admin-widget-item-for-listing d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
        <fieldset class="padding:default width:100% box-shadow-variant-1 d:flex justify-content:center">
            <legend class="bg:pure-black color:white padding:default">$moduleDirName</legend>
            <div class="admin-widget-information owl width:100%">
            <div class="text-on-admin-util text-highlight">$moduleName Module</div>
         
                <div class="form-group d:flex flex-gap:small">
                </div>
                
            </div>
        </fieldset>
    </li>
HTML;
            }
            ++$k;
        }
        return $htmlFrag;
    }
}