<?php

namespace App\Modules\Core\Data;

use App\InitLoader;
use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use JetBrains\PhpStorm\NoReturn;

class AppsData extends AbstractDataLayer
{
    /**
     * @throws \Exception
     */
    public function prepareAndGetAppListFrag(): array
    {
        $frag = []; $csrfToken = session()->getCSRFToken();

        $apps = InitLoader::getAllApps();
        $internal_modules = helper()->getModuleActivators([ModuleConfig::class, PluginConfig::class]);
        $updatesObject = AppConfig::getAppUpdatesObject();

        $k = 0;
        foreach ($apps as $path => $app){
            $classToString = $app::class;
            $updateInfos = [$classToString];
            if (isset($updatesObject['app']) && isset($updatesObject['app'][$classToString])){
                $updateInfos = $updatesObject['app'][$classToString];
            }

            $data = [
                ...$updateInfos,
                ...$app->info()
            ];

            $isInstalled = helper()->fileExists($path . DIRECTORY_SEPARATOR . '.installed');
            $installedFrag = <<<HTML
<form method="post" class="d:contents" action="/admin/tools/apps/install">
    <input type="hidden" name="token" value="$csrfToken">
    <input type="hidden" name="activator[]" value="$classToString">
    <button type="submit" class="listing-button color:black bg:white-one border:none border-width:default border:black padding:tiny
    margin-top:0 cursor:pointer">Install
    </button>
</form>
<form method="post" class="d:contents" action="/admin/tools/apps/delete">
    <input type="hidden" name="token" value="$csrfToken">
    <input type="hidden" name="activator[]" value="$classToString">
    <button data-click-onconfirmdelete="true" type="button" class="listing-button color:white bg:pure-black border:none border-width:default border:black padding:tiny
    margin-top:0 cursor:pointer">Delete
    </button>
</form>
HTML;
            if ($isInstalled){
                $installedFrag = <<<HTML
<form method="post" class="d:contents" action="/admin/tools/apps/uninstall">
    <input type="hidden" name="token" value="$csrfToken">
    <input type="hidden" name="activator[]" value="$classToString">
    <button type="submit" class="listing-button bg:white-one color:black border:none border-width:default border:black padding:tiny
    margin-top:0 cursor:pointer">UnInstall
    </button>
</form>
HTML;
            }

            $updateFrag = '';
            if (isset($data['can_update']) && $data['can_update']){
                $updateFrag =<<<FORM
<form method="post" class="d:contents" action="/admin/tools/apps/update">
                    <input type="hidden" name="token" value="$csrfToken">
                    <input type="hidden" name="activator[]" value="$classToString">
                    <button type="submit" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:small
        margin-top:0 cursor:pointer">Update
                    </button>
                </form>
FORM;
            }

            $type = 'Uncategorized';
            if (isset($data['type'])){
                $type = strtolower($data['type']);
                $type = ($type === 'module' || $type === 'modules') ? 'External Modules' : $type;
            }

            $type = ucfirst($type);
            if (!isset($frag[$type])){
                $frag[$type] = '';
            }

            $frag[$type] .= <<<HTML
<li data-list_id="$k" tabindex="0" class="d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
    <fieldset class="padding:default width:100% height:100% draggable d:flex justify-content:center">
        <div class="owl width:100%">
            <div class="text-on-admin-util text-highlight">{$data['name']}</div>
            <div class="form-group d:flex flex-gap:small flex-wrap:wrap">
            $installedFrag
            $updateFrag
            </div>

        </div>
    </fieldset>
</li>
HTML;
            ++$k;
        }

        $k = 0;
        foreach ($internal_modules as $module){
            /** @var $module PluginConfig **/
            $classToString = $module::class;
            $updateInfos = [];
            if (isset($updatesObject['module']) && isset($updatesObject['module'][$classToString])){
                $updateInfos = $updatesObject['module'][$classToString];
            }
            $data = [
                ...$updateInfos,
                ...$module->info()
            ];

            $updateFrag = '';
            if (isset($data['can_update']) && $data['can_update']){
                $updateFrag =<<<FORM
<form method="post" class="d:contents" action="/admin/tools/apps/update">
                    <input type="hidden" name="token" value="$csrfToken">
                    <input type="hidden" name="activator[]" value="$classToString">
                    <button type="submit" class="listing-button bg:pure-black color:white border:none border-width:default border:black padding:tiny
        margin-top:0 cursor:pointer">Update
                    </button>
                </form>
FORM;
            }

            if (!isset($frag['Module'])){
                $frag['Module'] = '';
            }

            $frag['Module'] .= <<<HTML
<li data-list_id="$k" tabindex="0" class="d:flex flex-d:column align-items:center justify-content:center cursor:pointer no-text-highlight">
    <fieldset class="padding:default width:100% height:100% draggable d:flex justify-content:center">
        <div class="owl width:100%">
            <div class="text-on-admin-util text-highlight">{$data['name']}</div>
            <div class="form-group d:flex flex-gap:small flex-wrap:wrap">
                $updateFrag
            </div>

        </div>
    </fieldset>
</li>
HTML;
            ++$k;
        }

        return $frag;

    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function handleAppRedirection(string $url, string $message = '')
    {
        if (headers_sent()){
            echo "<br>";
            echo <<<HTML
<a href="$url">$message</a>
HTML;
        }else{
            redirect($url);
        }
        exit(0);
    }

    /**
     * @throws \Exception
     */
    public function getAppObject(string $themeName)
    {
        $themeFullClass = "App\Apps\\$themeName\\{$themeName}Activator";
        $implements = class_implements($themeFullClass);
        if (is_array($implements) && key_exists(ModuleConfig::class, $implements)) {
            return new $themeFullClass;
        }

        return null;
    }
}