<?php

namespace App\Modules\Core\Controllers;

use App\InitLoader;
use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Data\AppsData;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\ThemeSystem;
use App\Modules\Core\States\AppsSystem;
use App\Modules\Core\States\UpdateMechanismState;
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

    /**
     * @throws \Exception
     */
    public function update()
    {
        $url = route('apps.index');
        if (input()->fromPost()->has('activator')){
            InitLoader::setEventStreamAsHTML(true);
            $appSystem = new AppsSystem(input()->fromPost()->retrieve('activator', []));
            $appSystem->setCurrentState(AppsSystem::OnAppUpdateState);
            $appSystem->runStates(false);
            InitLoader::setEventStreamAsHTML(false);
           if ($appSystem->getStateResult() === SimpleState::DONE){
               $this->appsData->handleAppRedirection($url, $appSystem->getSucessMessage());
           }
        }
        $this->appsData->handleAppRedirection($url, "An Error Occurred Updating App");
    }

    /**
     * @throws \Exception
     */
    public function delete()
    {
        if (input()->fromPost()->has('activator')){
            $appSystem = new AppsSystem(input()->fromPost()->retrieve('activator', []));
            $appSystem->setCurrentState(AppsSystem::OnAppDeleteState);
            $appSystem->runStates(false);
        }
        session()->flash(['An Error Occurred Deleting App'], []);
        redirect(route('apps.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function install(): void
    {
        if (input()->fromPost()->has('activator')){
            $appSystem = new AppsSystem(input()->fromPost()->retrieve('activator', []));
            $appSystem->setCurrentState(AppsSystem::OnAppActivateState);
            $appSystem->runStates(false);
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
            $appSystem->runStates(false);
        }
        session()->flash(['An Error Occurred UnInstalling App: Go Back'], []);
        redirect(route('apps.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function discover_updates(): void
    {
        InitLoader::setEventStreamAsHTML(true);
        $updateMechanismState = new UpdateMechanismState(types: ['module', 'app'], discoveredFrom: UpdateMechanismState::DiscoveredFromBrowser);
        helper()->addEventStreamHeader(1000000, 'text/html');
        $updateMechanismState->runStates(false);
        InitLoader::setEventStreamAsHTML(false);
        $url = route('apps.index');
        if ($updateMechanismState->getStateResult() === SimpleState::DONE){
            $this->appsData->handleAppRedirection($url, 'Update Check Done: Go Back');
        }
        $this->appsData->handleAppRedirection($url, 'An Error Occurred While Checking For App Updates: Go Back');
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