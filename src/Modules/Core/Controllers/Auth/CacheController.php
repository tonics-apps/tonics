<?php

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
     */
    #[NoReturn] public function clear()
    {
        $cacheKey = input()->fromGet()->retrieve('cache-key');
        $result = helper()->clearAPCUCache($cacheKey);

        response()->header('Cache-Result: ' . $result);
        response()->httpResponseCode(200);
        exit("Cache Cleared");
    }

    /**
     * @throws TonicsTemplateLoaderError
     * @throws \Exception
     */
    #[NoReturn] public function warmTemplateCache()
    {
        $templateLoader = new TonicsTemplateFileLoader('html');
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
     */
    #[NoReturn] public function unauthorizedAccess()
    {
        response()->httpResponseCode(400);
        response()->header('Cache-Result: ' . false);
        exit("Unauthorized Access");
    }

}