<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Data;

use App\Modules\Core\Boot\InitLoader;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\AbstractDataLayer;
use JetBrains\PhpStorm\NoReturn;

class AppsData extends AbstractDataLayer
{
    /**
     * @throws \Exception
     */
    public function getAppList(): array
    {
        $csrfToken = session()->getCSRFToken();

        $tdData = [];

        $apps = InitLoader::getAllApps();
        $internal_modules = helper()->getModuleActivators([ExtensionConfig::class]);
        $updatesObject = AppConfig::getAppUpdatesObject();

        /**  @var ExtensionConfig $app */
        foreach ($apps as $path => $app){
            $classToString = $app::class;

            $canUpdate = false;
            if (isset($updatesObject['app']) && isset($updatesObject['app'][$classToString])){
                $updateInfos = $updatesObject['app'][$classToString];
                $appTimeStamp = helper()->getTimeStampFromVersion($app->info()['version'] ?? '');
                $updateTimeStamp = $updateInfos['release_timestamp'] ?? '';
                $canUpdate = $updateTimeStamp > $appTimeStamp;
                $data = [
                    ...$updateInfos,
                    ...$app->info()
                ];
            } else {
                $data = $app->info();
            }

            $isInstalled = helper()->fileExists($path . DIRECTORY_SEPARATOR . '.installed');
            $installedFrag = <<<HTML
<form method="post" class="d:contents" action="/admin/tools/apps/install">
    <input type="hidden" name="token" value="$csrfToken">
    <input type="hidden" name="activator[]" value="$classToString">
    <button type="submit" class="color:black bg:white-one border:none border-width:default border:black padding:tiny
    margin-top:0 cursor:pointer">Install
    </button>
</form>
HTML;
            $settingsFrag = '';
            if ($isInstalled){
                $installedFrag = <<<HTML
<form method="post" class="d:contents" action="/admin/tools/apps/uninstall">
    <input type="hidden" name="token" value="$csrfToken">
    <input type="hidden" name="activator[]" value="$classToString">
    <button type="submit" class="bg:white-one color:black border:none border-width:default border:black padding:tiny
    margin-top:0 cursor:pointer">UnInstall
    </button>
</form>
HTML;
                if (isset($data['settings_page']) && !empty($data['settings_page'])){
                    $settingsFrag =<<<FORM
<a class="bg:pure-black color:white border:none border-width:default border:black padding:tiny
        margin-top:0 cursor:pointer" href="{$data['settings_page']}">Settings
</a>
FORM;
                }
            }

            $type = 'Uncategorized';
            if (isset($data['type'])){
                $type = strtolower($data['type']);
                $type = ($type === 'module' || $type === 'modules') ? 'External Modules' : $type;
            }

            $data['type'] = strtoupper($type);
            $data['update_available'] = ($canUpdate) ? 'Yes' : 'No';

            $data['update_frag'] = <<<HTML
<div class="form-group d:flex flex-gap:small flex-wrap:wrap">
            $installedFrag
            $settingsFrag
</div>
HTML;
            $tdData[] = $data;
        }

        foreach ($internal_modules as $module){
            /** @var $module ExtensionConfig **/
            $classToString = $module::class;
            $updateInfos = [];

            $canUpdate = false;
            if (isset($updatesObject['module']) && isset($updatesObject['module'][$classToString])){
                $updateInfos = $updatesObject['module'][$classToString];
                $moduleTimeStamp = helper()->getTimeStampFromVersion($module->info()['version'] ?? '');
                $updateTimeStamp = $updateInfos['release_timestamp'] ?? '';
                $canUpdate = $updateTimeStamp > $moduleTimeStamp;
            }
            $data = [
                ...$updateInfos,
                ...$module->info()
            ];

            $data['type'] = 'MODULE';

            $data['update_available'] = ($canUpdate) ? 'Yes' : 'No';

            $settingsFrag = '';
            if (isset($data['settings_page']) && !empty($data['settings_page'])){
                $settingsFrag =<<<FORM
<a class="bg:pure-black color:white border:none border-width:default border:black padding:tiny
        margin-top:0 cursor:pointer" href="{$data['settings_page']}">Settings
</a>
FORM;
            }

            $data['update_frag'] = <<<HTML
<div class="form-group d:flex flex-gap:small flex-wrap:wrap">
<button style="opacity: 50%" class="bg:pure-white color:black border:none border-width:default border:black padding:tiny
        margin-top:0 cursor:pointer pointer-events:none">Installed
</button>
            $settingsFrag
</div>
HTML;

            $tdData[] = $data;

        }

        return $tdData;
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
}