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

namespace App\Modules\Core\Controllers\Auth;

use App\Modules\Core\Configs\AppConfig;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateLoaderError;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateFileLoader;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use JetBrains\PhpStorm\NoReturn;

class CacheController
{

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (!hash_equals(AppConfig::getKey(), input()->fromGet()->retrieve('token', ''))) {
            $this->unauthorizedAccess();
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function clear()
    {
        $cacheKey = input()->fromGet()->retrieve('cache-key', '');
        $result = helper()->clearAPCUCache($cacheKey);

        response()->header('Cache-Result: ' . $result);
        response()->httpResponseCode(200);
        exit("Cache Cleared");
    }

    /**
     * @throws TonicsTemplateLoaderError
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function warmTemplateCache(): void
    {
        $templateLoader = new TonicsTemplateFileLoader('html', [AppConfig::getComposerPath()]);
        $templateLoader->resolveTemplateFiles(AppConfig::getModulesPath());
        $templateLoader->resolveTemplateFiles(AppConfig::getAppsPath());

        apcu_clear_cache();
        $view = AppConfig::initLoaderOthers()->getTonicsView();
        foreach ($templateLoader->getTemplates() as $templateKey => $template){
            # Warming cache
            $view->render(helper()->rtrim($templateKey, '.html'), TonicsView::RENDER_TOKENIZE_ONLY);
        }
        response()->header('Cache-Result: ' . 1);
        response()->httpResponseCode(200);
        exit("Cache Warmed");
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function unauthorizedAccess(): void
    {
        response()->httpResponseCode(400);
        response()->header('Cache-Result: ' . false);
        exit("Unauthorized Access");
    }

}