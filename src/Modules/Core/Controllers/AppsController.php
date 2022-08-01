<?php

namespace App\Modules\Core\Controllers;

use App\InitLoader;
use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Data\AppsData;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\ThemeSystem;
use App\Modules\Core\States\AppsSystem;
use Devsrealm\TonicsFileManager\Utilities\FileHelper;
use JetBrains\PhpStorm\NoReturn;

class AppsController
{
    use FileHelper;

    private AppsData $appsData;

    /**
     * @param AppsData $appsData
     */
    public function __construct(AppsData $appsData)
    {
        $this->appsData = $appsData;
    }

    /**
     * @throws \Exception
     */
    public function index(): void
    {
        view('Modules::Core/Views/App/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'AppListingFrag' => $this->appsData->prepareAndGetAppListFrag(),
        ]);

    }

    public function update()
    {

    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function install(): void
    {
        if (input()->fromPost()->has('activator')){
            $appSystem = new AppsSystem(input()->fromPost()->retrieve('activator', []));
            $appSystem->setCurrentState(AppsSystem::OnAppActivateState);
            $appSystem->runStates();
            if ($appSystem->getStateResult() === SimpleState::DONE){
                redirect(route('apps.index'));
            }
        }
        session()->flash(['An Error Occurred Installing App'], []);
        redirect(route('apps.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function uninstall(): void
    {
        if (input()->fromPost()->has('activator')){
            $appSystem = new AppsSystem(input()->fromPost()->retrieve('activator', []));
            $appSystem->setCurrentState(AppsSystem::OnAppDeActivateState);
            $appSystem->runStates();
            if ($appSystem->getStateResult() === SimpleState::DONE){
                redirect(route('apps.index'));
            }
        }
        session()->flash(['An Error Occurred UnInstalling App'], []);
        redirect(route('apps.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function serve(string $appName): void
    {
        $path = AppConfig::getAppsPath() . "/$appName/Assets/" . request()->getParam('path');
        $ext = helper()->extension($path);
        if (helper()->fileExists($path)){
            $aliasPath = DriveConfig::xAccelAppFilePath() . "$appName/Assets/" . request()->getParam('path');
            $mime = match ($ext) {
                'css' => 'text/css',
                'js' => 'application/javascript',
                default => mime_content_type($path),
            };
            $this->serveDownloadableFile($aliasPath, helper()->fileSize($path), false, $mime);
        }
        die("Theme Resource Doesn't Exist");
    }
    /**
     * @return AppsData
     */
    public function getAppsData(): AppsData
    {
        return $this->appsData;
    }
}