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

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Boot\InitLoader;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\CoreActivator;
use App\Modules\Core\Data\AppsData;
use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\States\AppsSystem;
use App\Modules\Core\States\UpdateMechanismState;
use App\Modules\Field\Data\FieldData;
use Devsrealm\TonicsFileManager\Utilities\FileHelper;
use JetBrains\PhpStorm\NoReturn;
use function GuzzleHttp\Psr7\str;

class AppsController
{
    use FileHelper;

    private AppsData $appsData;
    private ?FieldData $fieldData;

    /**
     * @param AppsData $appsData
     * @param FieldData|null $fieldData
     */
    public function __construct(AppsData $appsData, FieldData $fieldData = null)
    {
        $this->appsData = $appsData;
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function index(): void
    {

        $dataTableHeaders = [
            ['type' => '', 'title' => 'Name', 'slug' => 'name', 'minmax' => '150px, 1fr', 'td' => 'name'],
            ['type' => '', 'title' => 'Description', 'slug' => 'description', 'minmax' => '300px, 1.6fr', 'td' => 'description'],
            ['type' => '', 'title' => 'Type', 'slug' => 'type', 'minmax' => '150px, 1fr', 'td' => 'type'],
            ['type' => '', 'title' => 'Actions', 'minmax' => '150px, 1fr', 'td' => 'update_frag'],
            ['type' => '', 'title' => 'Update Available', 'minmax' => '100px, .7fr', 'td' => 'update_available'],
            ['type' => '', 'title' => 'Version', 'minmax' => '100px, .7fr', 'td' => 'version'],
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
        helper()->updateActivateEventStreamMessage();

        $entityBag = null;
        if ($this->getAppsData()->isDataTableType(AbstractDataLayer::DataTableEventTypeDelete,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            $deleteActivators = $this->getToDeletesActivators($entityBag);
            $error = "An Error Occurred Deleting App";
            if (!empty($deleteActivators)){
                $appSystem = new AppsSystem($deleteActivators);
                $appSystem->setCurrentState(AppsSystem::OnAppDeleteState)->setMessageDebug(false);
                $appSystem->runStates(false);

                if ($appSystem->getStateResult() === SimpleState::DONE ){
                    helper()->updateActivateEventStreamMessage(1);
                    response()->onSuccess([], $appSystem->getSucessMessage(), more: AbstractDataLayer::DataTableEventTypeDelete);
                } else {
                    $error = $appSystem->getErrorMessage();
                }
            }

            helper()->updateActivateEventStreamMessage(1);
            response()->onError(500, $error);

        } elseif ($this->getAppsData()->isDataTableType(AbstractDataLayer::DataTableEventTypeAppUpdate,
            getEntityDecodedBagCallable: function ($decodedBag) use (&$entityBag) {
                $entityBag = $decodedBag;
            })) {
            $updateActivators = $this->getToUpdateActivators($entityBag);
            $error = "An Error Occurred Updating App";
            if (!empty($updateActivators)){
                $appSystem = new AppsSystem($updateActivators);
                $appSystem->setCurrentState(AppsSystem::OnAppUpdateState)->setMessageDebug(false);
                $appSystem->runStates(false);

                if ($appSystem->getStateResult() === SimpleState::DONE){
                    helper()->updateActivateEventStreamMessage(1);
                    response()->onSuccess([], $appSystem->getSucessMessage(), more: AbstractDataLayer::DataTableEventTypeAppUpdate);
                } else {
                    $error = $appSystem->getErrorMessage();
                }
            }

            helper()->updateActivateEventStreamMessage(1);
            response()->onError(500, $error);
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
     * @throws \Throwable
     */
    #[NoReturn] public function install(): void
    {
        $appSystem = new AppsSystem(input()->fromPost()->retrieve('activator', []));
        if (input()->fromPost()->has('activator')){
            $appSystem->setCurrentState(AppsSystem::OnAppActivateState);
            $appSystem->runStates(false);
        }

        if ($appSystem->getStateResult() !== SimpleState::DONE){
            session()->flash(['An Error Occurred Installing App'], []);
        }

        redirect(route('apps.index'));
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function uninstall(): void
    {
        $appSystem = new AppsSystem(input()->fromPost()->retrieve('activator', []));
        if (input()->fromPost()->has('activator')){
            $appSystem->setCurrentState(AppsSystem::OnAppDeActivateState);
            $appSystem->runStates(false);
        }

        if ($appSystem->getStateResult() !== SimpleState::DONE){
            session()->flash(['An Error Occurred UnInstalling App: Go Back'], []);
        }

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
    public function uploadForm(): void
    {
        $fieldItems = $this->getFieldData()->generateFieldWithFieldSlug(
            ['upload-app-page']
        )->getHTMLFrag();

        view('Modules::Core/Views/App/app_upload', [
                'FieldItems' => $fieldItems,
            ]
        );
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function upload(): void
    {
        $url = route('apps.index');
        $message = 'An Error Occurred While Uploading App';
        if (input()->fromPost()->hasValue('plugin_url')){
            $pluginURL = input()->fromPost()->retrieve('plugin_url');
            if (parse_url($pluginURL, PHP_URL_HOST) === null){
                $pluginURL = AppConfig::getAppUrl() . input()->fromPost()->retrieve('plugin_url');
            }
            InitLoader::setEventStreamAsHTML(true);
            helper()->addEventStreamHeader(1000000, 'text/html');

            try {
                $appSystem = new AppsSystem();
                $appSystem->setPluginURL($pluginURL);
                $appSystem->setCurrentState(AppsSystem::OnAppUploadState);
                $appSystem->runStates(false);
                InitLoader::setEventStreamAsHTML(false);
                if ($appSystem->getStateResult() === SimpleState::DONE){
                    $message = $appSystem->getSucessMessage();
                    session()->flash([$message], type: Session::SessionCategories_FlashMessageSuccess);
                } else {
                    $message = $message . ': Go Back';
                }
            }catch (\Throwable $exception){
                // Log..
            }

        }
        session()->flash([$message]);
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
        if (!helper()->fileExists($path)){
            die("Resource Doesn't Exist");
        }

        $aliasPath = $xAccelPath . "$moduleAppName/Assets/" . $requestPath;
        $mime = match ($ext) {
            'css' => 'text/css',
            'js' => 'text/javascript',
            default => mime_content_type($path),
        };

        $this->serveDownloadableFile($aliasPath, helper()->fileSize($path), false, $mime);
    }


    /**
     * Normalizes the given pathname by removing control characters and other invalid characters.
     *
     * @param string $string The pathname to normalize.
     * @return string The normalized pathname.
     * @throws \Exception
     */
    private function normalizePathname(string $string): string
    {
        // the preg_replace change multiple slashes to one
        $string = preg_replace("#//+#", "\\1/", $string);

        // Define control characters and other invalid characters to be removed
        $controlChar = range(chr(0), chr(31));
        $controlChar[] = chr(127);
        $invalidChars = [
            '?', '[', ']', '\\',  '=', '<', '>', ':', ';', ',', "'",
            '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!',
            '{', '}', '%', '+', '’', '«', '»', '”', '“', ...$controlChar];

        // Remove control characters and other invalid characters
        return str_ireplace($invalidChars, '', $string);
    }


    /**
     * @return AppsData
     */
    public function getAppsData(): AppsData
    {
        return $this->appsData;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }

}