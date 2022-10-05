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

use App\Library\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Boot\InitLoader;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\CoreActivator;
use App\Modules\Core\Data\AppsData;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
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

        $dataTableHeaders = [
            ['type' => '', 'title' => 'Name', 'slug' => 'name', 'minmax' => '150px, .6fr', 'td' => 'name'],
            ['type' => '', 'title' => 'Description', 'slug' => 'description', 'minmax' => '300px, 1.6fr', 'td' => 'description'],
            ['type' => '', 'title' => 'Type', 'slug' => 'type', 'minmax' => '50px, 1fr', 'td' => 'type'],
            ['type' => '', 'title' => 'Actions', 'minmax' => '50px, 1fr', 'td' => 'update_frag'],
            ['type' => '', 'title' => 'Update Available', 'minmax' => '35px, .7fr', 'td' => 'update_available'],
        ];

        view('Modules::Core/Views/App/index', [
            'DataTable' => [
                'headers' => $dataTableHeaders,
                'paginateData' => [
                    'data' => $this->appsData->getAppList()
                ],
                'dataTableType' => 'APPLICATION_VIEW',
            ],
            'SiteURL' => AppConfig::getAppUrl(),
        ]);

    }

    /**
     * @throws \Exception
     */
    public function dataTable(): void
    {
        $entityBag = null;
        if ($this->getAppsData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            $deleteActivators = $this->getToDeletesActivators($entityBag);
            $error = "An Error Occurred Deleting App";
            if (!empty($deleteActivators)){
                $appSystem = new AppsSystem($deleteActivators);
                $appSystem->setCurrentState(AppsSystem::OnAppDeleteState);
                $appSystem->runStates(false);
                if ($appSystem->getStateResult() === SimpleState::DONE ){
                    response()->onSuccess([], $appSystem->getSucessMessage(), more: AbstractDataLayer::DataTableEventTypeDelete);
                } else {
                    $error = $appSystem->getErrorMessage();
                }
            }

            response()->onError(500, $error);
        } elseif ($this->getAppsData()->isDataTableType(AbstractDataLayer::DataTableEventTypeAppUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
                $updateActivators = $this->getToUpdateActivators($entityBag);
                dd($updateActivators);
            })) {
            if ($this->updateMultiple($entityBag)) {
                response()->onSuccess([], "Records Updated", more: AbstractDataLayer::DataTableEventTypeUpdate);
            } else {
                response()->onError(500);
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function getToDeletesActivators($entityBag): array
    {
        $activators = [];
        $deleteItems = $this->getAppsData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveDeleteElements, $entityBag);
        $apps = InitLoader::getAllApps();
        foreach ($apps as  $app){
            $classToString = $app::class;
            /** @var  $app ExtensionConfig */
            $name = (isset($app->info()['name'])) ? $app->info()['name'] : '';
            foreach ($deleteItems as $item){
                if (!isset($item->name)){ continue; }
                if ($name === $item->name){
                    $activators[] = $classToString;
                }
            }
        }

        return $activators;
    }

    /**
     * @param $entityBag
     * @return array
     * @throws \Exception
     */
    private function getToUpdateActivators($entityBag): array
    {
        $activators = [];
        $updateItems = $this->getAppsData()->retrieveDataFromDataTable(AbstractDataLayer::DataTableRetrieveAppUpdateElements, $entityBag);
        $apps = InitLoader::getAllApps();
        $internal_modules = helper()->getModuleActivators([ExtensionConfig::class]);
        foreach ($apps as  $app){
            $classToString = $app::class;
            /** @var  $app ExtensionConfig */
            $name = (isset($app->info()['name'])) ? $app->info()['name'] : '';
            foreach ($updateItems as $item){
                if (!isset($item->name)){ continue; }
                if (!isset($item->type)){ continue; }

                if (strtoupper($item->type) === 'MODULE'){
                    continue;
                }

                if ($name === $item->name){
                    $activators[] = $classToString;
                }
            }
        }

        foreach ($internal_modules as $module){
            $classToString = $module::class;
            /** @var  $module ExtensionConfig */
            $name = (isset($module->info()['name'])) ? $module->info()['name'] : '';
            foreach ($updateItems as $item){
                if (!isset($item->name)){ continue; }
                if (!isset($item->type)){ continue; }
                if (strtoupper($item->type) === 'MODULE'){
                    if ($name === $item->name){
                        $activators[] = $classToString;
                    }
                }
            }
        }

        #
        # On every update request, we include the CoreActivator since it is mandatory for every
        # dependency, and besides, it should always be the latest version, this should be
        # replaced by a dependency graph which is not currently supported and might never will.
        #
        if (!empty($activators)){
            $coreFound = false;
            foreach ($activators as $activator){
                if (CoreActivator::class === $activator){
                    $coreFound = true;
                }
            }
            if (!$coreFound){
                $activators = [CoreActivator::class, ...$activators];
            }
        }

        return $activators;
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
            # replaced by a dependency graph which is not currently supported and might never will.
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

            if ($appSystem->getStateResult() === SimpleState::ERROR ){
                session()->flash([$appSystem->getErrorMessage()], []);
            } else {
                session()->flash([$appSystem->getErrorMessage()], [], Session::SessionCategories_FlashMessageSuccess);
            }
            redirect(route('apps.index'));
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
    #[NoReturn] public function serveAppAsset(string $appName): void
    {
        $this->serve(DriveConfig::xAccelAppFilePath(), AppConfig::getAppsPath(), $appName);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function serveModuleAsset(string $moduleName): void
    {
        $this->serve(DriveConfig::xAccelModuleFilePath(), AppConfig::getModulesPath(), $moduleName);
    }

    /**
     * @param string $xAccelPath
     * @param string $appModulesPath
     * @param string $moduleAppName
     * @return void
     * @throws \Exception
     */
    #[NoReturn] protected function serve(string $xAccelPath, string $appModulesPath, string $moduleAppName): void
    {
        $requestPath = @trim(request()->getParam('path'), '/');
        if (empty($requestPath)){
            die("Resource Doesn't Exist");
        }

        # Normalize FileName
        # remove the ext since it would have been mangled by normalize
        # and re-add it
        $requestPath = $this->normalizePathname($requestPath);
        $path = $appModulesPath . "/$moduleAppName/Assets/" . $requestPath;
        $ext = helper()->extension($path);
        if (helper()->fileExists($path)){
            $aliasPath = $xAccelPath . "$moduleAppName/Assets/" . $requestPath;
            $mime = match ($ext) {
                'css' => 'text/css',
                'js' => 'text/javascript',
                default => mime_content_type($path),
            };
            $this->serveDownloadableFile($aliasPath, helper()->fileSize($path), false, $mime);
        }

        die("Resource Doesn't Exist");
    }


    /**
     * @throws \Exception
     */
    private function normalizePathname(string $string): string
    {
        // the preg_replace change multiple slashes to one
        return preg_replace("#//+#", "\\1/", $string);
    }


    /**
     * @return AppsData
     */
    public function getAppsData(): AppsData
    {
        return $this->appsData;
    }

}