<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\States;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Media\FileManager\LocalDriver;
use JetBrains\PhpStorm\NoReturn;

class AppsSystem extends SimpleState
{

    private array $activatorsFromPost;
    private array $allActivators;

    private string $pluginURL = '';

    const OnAppActivateState = 'OnAppActivateState';
    const OnAppPermissionErrorState = 'OnAppPermissionErrorState';
    const OnAppProcessActivationState = 'OnAppProcessActivationState';

    const OnAppDeActivateState = 'OnAppDeActivateState';
    const OnAppProcessDeActivationState = 'OnAppProcessDeActivationState';

    const OnAppDeleteState = 'OnAppDeleteState';
    const OnAppProcessDeletionState = 'OnAppProcessDeletionState';

    const OnAppUpdateState = 'OnAppUpdateState';
    const OnAppProcessUpdateState = 'OnAppProcessUpdateState';

    const OnAppUploadState = 'OnAppUploadState';
    const OnAppProcessUploadState = 'OnAppProcessUploadState';

    private bool $messageDebug = true;

    /**
     * @throws \Exception
     */
    public function __construct($activatorsFromPost = [])
    {
        $this->allActivators = [
            ...helper()->getAppsActivator([ExtensionConfig::class], installed: false),
            ...helper()->getAppsActivator([ExtensionConfig::class], helper()->getAllModulesDirectory(), false)
        ];

        $this->activatorsFromPost = $activatorsFromPost;
    }

    /**
     * @throws \Exception
     */
    public function OnAppActivateState(): string
    {
        if ($this->permissionOK()){
           return $this->switchState(self::OnAppProcessActivationState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function OnAppPermissionErrorState(): string
    {
        session()->flash(['An Error Occurred Reading or Writing File'], []);
        redirect(route('apps.index'));
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function OnAppProcessActivationState(): string
    {
        $errorActivatorName = []; $installedApp = [];
        foreach ($this->activatorsFromPost as $activatorPost){
            if (isset($this->allActivators[$activatorPost])){
                /** @var ExtensionConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                if (($appDirPath = helper()->getClassDirectory($activator)) !== false && AppConfig::isAppNameSpace($activator)){
                    $installedFilePath = $appDirPath . DIRECTORY_SEPARATOR . '.installed';
                    # You can't activate an internal module, this is to avoid cases where an app is using a module namespace
                    if ($this->isInternalModulePath($appDirPath) === true){
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        continue;
                    }
                    $result = @file_put_contents($installedFilePath, '');
                    if ($result === false){
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    } else {
                        $activator->onInstall();
                        $installedApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    }
                }
            }
        }

        if (!empty($errorActivatorName)){
            $errorActivatorName = implode(',', $errorActivatorName);
            session()->flash(["An Error Occurred Installing App: [$errorActivatorName]"], []);
            redirect(route('apps.index'));
        }

        if (!empty($installedApp)){
            apcu_clear_cache();
            $installedApp = implode(',', $installedApp);
            session()->flash(["[$installedApp] App Installed"], [], type: Session::SessionCategories_FlashMessageSuccess);
            AppConfig::updateRestartService();
            return self::DONE;
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function OnAppDeActivateState(): string
    {
        if ($this->permissionOK()){
            return $this->switchState(self::OnAppProcessDeActivationState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function OnAppProcessDeActivationState(): string
    {
        $errorActivatorName = []; $unInstalledApp = [];
        foreach ($this->activatorsFromPost as $activatorPost){
            if (isset($this->allActivators[$activatorPost])){
                /** @var ExtensionConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                if (($appDirPath = helper()->getClassDirectory($activator)) !== false && AppConfig::isAppNameSpace($activator)){
                    $installedFilePath = $appDirPath . DIRECTORY_SEPARATOR . '.installed';
                    # You can't de-activate an internal module, this is to avoid cases where an app is using a module namespace
                    if ($this->isInternalModulePath($appDirPath) === false && helper()->fileExists($installedFilePath) && helper()->forceDeleteFile($installedFilePath)){
                        $activator->onUninstall();
                        $unInstalledApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    } else {
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    }
                }
            }
        }

        if (!empty($errorActivatorName)){
            $errorActivatorName = implode(',', $errorActivatorName);
            session()->flash(["An Error Occurred UnInstalling App: [$errorActivatorName]"], []);
            redirect(route('apps.index'));
        }

        if (!empty($unInstalledApp)){
            apcu_clear_cache();
            $unInstalledApp = implode(',', $unInstalledApp);
            session()->flash(["[$unInstalledApp] App UnInstalled"], [], type: Session::SessionCategories_FlashMessageSuccess);
            AppConfig::updateRestartService();
            return self::DONE;
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function OnAppDeleteState(): string
    {
        if ($this->permissionOK()){
            return $this->switchState(self::OnAppProcessDeletionState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function OnAppProcessDeletionState(): string
    {
        $errorActivatorName = []; $deletedApp = [];
        foreach ($this->activatorsFromPost as $activatorPost){
            if (isset($this->allActivators[$activatorPost])){
                /** @var ExtensionConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                if (($appDirPath = helper()->getClassDirectory($activator)) !== false && AppConfig::isAppNameSpace($activator)){
                    $installedFilePath = $appDirPath . DIRECTORY_SEPARATOR . '.installed';

                    # You can't delete an app that has .installed file
                    if (helper()->fileExists($installedFilePath)){
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        continue;
                    }
                    # You can't delete an internal module (the AppNameSpace check should have prevented it but 2 check ain't bad)
                    if ($this->isInternalModulePath($appDirPath)){
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        continue;
                    }

                    # Okay, Things are fine, remove the app
                    try {
                        $activator->onDelete();
                        if(helper()->deleteDirectory($appDirPath)){
                            $deletedApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        } else {
                            $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        }
                    } catch (\Exception $exception){
                        // Log..
                    }
                }
            }
        }

        if (!empty($errorActivatorName)){
            $errorActivatorName = implode(', ', $errorActivatorName);
            $this->setErrorMessage("An Error Occurred Deleting App: [$errorActivatorName]");
        } elseif (!empty($deletedApp)){
            $deletedApp = implode(', ', $deletedApp);
            $this->setSucessMessage("[$deletedApp] App Deleted");
            AppConfig::updateRestartService();
            return self::DONE;
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function OnAppUpdateState(): string
    {
        if ($this->permissionOK()){
            return $this->switchState(self::OnAppProcessUpdateState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function OnAppProcessUpdateState(): string
    {
        $appOrModuleToUpdate = [];
        foreach ($this->activatorsFromPost as $activatorPost) {
            if (isset($this->allActivators[$activatorPost])) {
                /** @var ExtensionConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                if (($appDirPath = helper()->getClassDirectory($activator)) !== false){

                    # is module
                    if (str_starts_with($appDirPath, AppConfig::getModulesPath())){
                        $moduleUpdate = new UpdateMechanismState();
                        $moduleUpdate->reset()->setUpdates([helper()->getFileName($appDirPath)])->setTypes(['module'])->setAction('update')
                            ->runStates(false);
                        if ($moduleUpdate->getStateResult() === SimpleState::ERROR){
                            return self::ERROR;
                        }
                    }

                    # is app
                    if (str_starts_with($appDirPath, AppConfig::getAppsPath())){
                        $appUpdate = new UpdateMechanismState();
                        $appUpdate->reset()->setUpdates([helper()->getFileName($appDirPath)])->setTypes(['app'])->setAction('update')
                            ->runStates(false);
                        if ($appUpdate->getStateResult() === SimpleState::ERROR){
                            return self::ERROR;
                        }
                    }

                    $appOrModuleToUpdate[] = helper()->getFileName($appDirPath);
                }
            }
        }

        AppConfig::updateRestartService();
        $appOrModuleToUpdate = implode(', ', $appOrModuleToUpdate);
        $this->setSucessMessage("[$appOrModuleToUpdate] Updated: Reload Page (If Any, Migrations Scheduled)");
        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function OnAppUploadState(): string
    {
        # Checking Only App Permission Since You Can Only Install Inside The App Directory
        if ($this->appPermissionOk()){
            return $this->switchState(self::OnAppProcessUploadState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function OnAppProcessUploadState(): string
    {
        $localDriver = new LocalDriver();
        $name = helper()->randomString(15);
        $zipName = $name . '.zip'; $tempPath = DriveConfig::getTempPathForApps();
        if ($localDriver->createFromURL($this->getPluginURL(), $tempPath, $zipName, importToDB: false)){
            $extractToTemp = $tempPath . DIRECTORY_SEPARATOR . $name;
            $pathToArchive = $tempPath . DIRECTORY_SEPARATOR. $zipName;
            $result = helper()->createDirectoryRecursive($extractToTemp);
            if (!$result){
                return self::ERROR;
            }
            $extractedFileResult = $localDriver->extractFile($pathToArchive, $extractToTemp, importToDB: false);
            if ($extractedFileResult === true){
                $dir = array_filter(glob($extractToTemp . DIRECTORY_SEPARATOR .'*'), 'is_dir');
                if (!empty($dir)){
                    return $this->OnAppFinalizeUpload($dir);
                }
            }
        }

        return self::ERROR;
    }

    /**
     * @param $dir
     * @return string
     * @throws \Exception
     */
    public function OnAppFinalizeUpload($dir): string
    {
        # It should only contain one folder which should be the name of the app
        if (count($dir) === 1){
            $appTempPath = $dir[0];
            $activatorFile = helper()->findFilesWithExtension(['php'], $appTempPath) ?? [];
            # Should only have one PHP File at the Root which should be the activator
            if (count($activatorFile) === 1 && ($getFileContent = @file_get_contents($activatorFile[0]))){
               $class = helper()->getFullClassName($getFileContent);
                if (AppConfig::isAppNameSpace($class)){
                    $appName = helper()->getFileName($appTempPath);
                    $copyResult = helper()->copyFolder($appTempPath, AppConfig::getAppsPath() . DIRECTORY_SEPARATOR . $appName);
                    if ($copyResult === true) {
                        AppConfig::updateRestartService();
                        $this->setSucessMessage("[$appName] Uploaded: Go To App List Page, Ignore if on App Page");
                        return self::DONE;
                    }
                }
            }
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function appPermissionOk(): bool
    {
        return helper()->isReadable(AppConfig::getAppsPath()) && helper()->isWritable(AppConfig::getAppsPath());
    }

    /**
     * @throws \Exception
     */
    public function modulePermissionOk(): bool
    {
        return helper()->isReadable(AppConfig::getModulesPath()) && helper()->isWritable(AppConfig::getModulesPath());
    }

    /**
     * @throws \Exception
     */
    public function permissionOK(): bool
    {
        return helper()->isReadable(AppConfig::getAppsPath()) && helper()->isWritable(AppConfig::getAppsPath())
            && helper()->isReadable(AppConfig::getModulesPath()) && helper()->isWritable(AppConfig::getModulesPath());
    }
    
    public function isInternalModulePath($appDir): bool
    {
        return str_starts_with($appDir, AppConfig::getModulesPath());
    }


    /**
     * @return array
     */
    public function getActivatorsFromPost(): array
    {
        return $this->activatorsFromPost;
    }

    /**
     * @param array $activatorsFromPost
     */
    public function setActivatorsFromPost(array $activatorsFromPost): void
    {
        $this->activatorsFromPost = $activatorsFromPost;
    }

    /**
     * @return array
     */
    public function getAllActivators(): array
    {
        return $this->allActivators;
    }

    /**
     * @param array $allActivators
     */
    public function setAllActivators(array $allActivators): void
    {
        $this->allActivators = $allActivators;
    }

    /**
     * @return string
     */
    public function getPluginURL(): string
    {
        return $this->pluginURL;
    }

    /**
     * @param string $pluginURL
     */
    public function setPluginURL(string $pluginURL): void
    {
        $this->pluginURL = $pluginURL;
    }

    /**
     * @return bool
     */
    public function isMessageDebug(): bool
    {
        return $this->messageDebug;
    }

    /**
     * @param bool $messageDebug
     * @return AppsSystem
     */
    public function setMessageDebug(bool $messageDebug): AppsSystem
    {
        $this->messageDebug = $messageDebug;
        return $this;
    }

}