<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Boot\InitLoader;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\CoreActivator;
use App\Modules\Core\Data\AppsData;
use App\Modules\Core\Library\SimpleState;
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
            'AppListingFrag' => $this->appsData->prepareAndGetAppListFrag(),
        ]);

    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function update(): void
    {
        $url = route('apps.index');
        if (input()->fromPost()->has('activator')){

            InitLoader::setEventStreamAsHTML(true);
            $updateActivator = input()->fromPost()->retrieve('activator', []);

            #
            # On every update request, we include the CoreActivator since it is mandatory for every
            # dependency, and besides, it should always be the latest version, this should be
            # replaced by a dependency graph which is not currently supported.
            #
            if (!empty($updateActivator)){
                $coreFound = false;
                foreach ($updateActivator as $activator){
                    if (CoreActivator::class === $activator){
                        $coreFound = true;
                    }
                }
                if (!$coreFound){
                    $updateActivator = [CoreActivator::class, ...$updateActivator];
                }
            }

            $appSystem = new AppsSystem($updateActivator);
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
    #[NoReturn] public function delete(): void
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
    #[NoReturn] public function upload(): void
    {
        $url = route('apps.index');
        $message = 'An Error Occurred While Uploading App: Go Back';
        if (input()->fromPost()->has('plugin_url')){
            InitLoader::setEventStreamAsHTML(true);
            helper()->addEventStreamHeader(1000000, 'text/html');

            $appSystem = new AppsSystem();
            $appSystem->setPluginURL(input()->fromPost()->retrieve('plugin_url'));
            $appSystem->setCurrentState(AppsSystem::OnAppUploadState);
            $appSystem->runStates(false);

            InitLoader::setEventStreamAsHTML(false);
            if ($appSystem->getStateResult() === SimpleState::DONE){
                $message = $appSystem->getSucessMessage();
            }
        }
        $this->appsData->handleAppRedirection($url, $message);
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