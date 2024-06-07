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

namespace App\Modules\Core\States;

use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\RequestInterceptor\RefreshTreeSystem;
use App\Modules\Core\Services\AppInstallationService;
use JetBrains\PhpStorm\NoReturn;

class AppsSystem extends SimpleState
{

    const OnAppActivateState            = 'OnAppActivateState';
    const OnAppPermissionErrorState     = 'OnAppPermissionErrorState';
    const OnAppProcessActivationState   = 'OnAppProcessActivationState';
    const OnAppDeActivateState          = 'OnAppDeActivateState';
    const OnAppProcessDeActivationState = 'OnAppProcessDeActivationState';
    const OnAppDeleteState              = 'OnAppDeleteState';
    const OnAppProcessDeletionState     = 'OnAppProcessDeletionState';
    const OnAppUpdateState              = 'OnAppUpdateState';
    const OnAppProcessUpdateState       = 'OnAppProcessUpdateState';
    const OnAppUploadState              = 'OnAppUploadState';
    const OnAppProcessUploadState       = 'OnAppProcessUploadState';
    private array                   $activatorsFromPost;
    private array                   $allActivators;
    private string                  $pluginURL              = '';
    private string                  $pluginSignatureHash    = '';
    private bool                    $messageDebug           = true;
    private ?AppInstallationService $appInstallationService = null;

    /**
     * @throws \Exception
     */
    public function __construct ($activatorsFromPost = [], AppInstallationService $appInstallationService = null)
    {
        $this->allActivators = [
            ...helper()->getAppsActivator([ExtensionConfig::class], installed: false),
            ...helper()->getAppsActivator([ExtensionConfig::class], helper()->getAllModulesDirectory(), false),
        ];

        $this->activatorsFromPost = $activatorsFromPost;
    }

    /**
     * @throws \Exception
     */
    public function OnAppActivateState (): string
    {
        if ($this->permissionOK()) {
            return $this->switchState(self::OnAppProcessActivationState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function OnAppPermissionErrorState (): string
    {
        session()->flash(['An Error Occurred Reading or Writing File'], []);
        redirect(route('apps.index'));
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     * @throws \Throwable
     */
    public function OnAppProcessActivationState (): string
    {
        $errorActivatorName = [];
        $installedApp = [];
        foreach ($this->activatorsFromPost as $activatorPost) {
            if (isset($this->allActivators[$activatorPost])) {
                /** @var ExtensionConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                if (($appDirPath = helper()->getClassDirectory($activator)) !== false && AppConfig::isAppNameSpace($activator)) {
                    $installedFilePath = $appDirPath . DIRECTORY_SEPARATOR . '.installed';
                    # You can't activate an internal module, this is to avoid cases where an app is using a module namespace
                    if ($this->isInternalModulePath($appDirPath) === true) {
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        continue;
                    }
                    $result = @file_put_contents($installedFilePath, '');
                    if ($result === false) {
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    } else {
                        $activator->onInstall();
                        $installedApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    }
                }
            }
        }

        if (!empty($errorActivatorName)) {
            $errorActivatorName = implode(',', $errorActivatorName);
            session()->flash(["An Error Occurred Installing App: [$errorActivatorName]"], []);
            redirect(route('apps.index'));
        }

        if (!empty($installedApp)) {
            apcu_clear_cache();
            $installedApp = implode(',', $installedApp);
            session()->flash(["[$installedApp] App Installed"], [], type: Session::SessionCategories_FlashMessageSuccess);
            AppConfig::updateRestartService();
            RefreshTreeSystem::RefreshTreeSystem();
            return self::DONE;
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function OnAppDeActivateState (): string
    {
        if ($this->permissionOK()) {
            return $this->switchState(self::OnAppProcessDeActivationState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function OnAppProcessDeActivationState (): string
    {
        $errorActivatorName = [];
        $unInstalledApp = [];
        foreach ($this->activatorsFromPost as $activatorPost) {
            if (isset($this->allActivators[$activatorPost])) {
                /** @var ExtensionConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                if (($appDirPath = helper()->getClassDirectory($activator)) !== false && AppConfig::isAppNameSpace($activator)) {
                    $installedFilePath = $appDirPath . DIRECTORY_SEPARATOR . '.installed';
                    # You can't de-activate an internal module, this is to avoid cases where an app is using a module namespace
                    if ($this->isInternalModulePath($appDirPath) === false && helper()->fileExists($installedFilePath) && helper()->forceDeleteFile($installedFilePath)) {
                        $activator->onUninstall();
                        $unInstalledApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    } else {
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    }
                }
            }
        }

        if (!empty($errorActivatorName)) {
            $errorActivatorName = implode(',', $errorActivatorName);
            session()->flash(["An Error Occurred UnInstalling App: [$errorActivatorName]"], []);
            redirect(route('apps.index'));
        }

        if (!empty($unInstalledApp)) {
            apcu_clear_cache();
            $unInstalledApp = implode(',', $unInstalledApp);
            session()->flash(["[$unInstalledApp] App UnInstalled"], [], type: Session::SessionCategories_FlashMessageSuccess);
            AppConfig::updateRestartService();
            RefreshTreeSystem::RefreshTreeSystem();
            return self::DONE;
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function OnAppDeleteState (): string
    {
        if ($this->permissionOK()) {
            return $this->switchState(self::OnAppProcessDeletionState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function OnAppProcessDeletionState (): string
    {
        $errorActivatorName = [];
        $deletedApp = [];
        foreach ($this->activatorsFromPost as $activatorPost) {
            if (isset($this->allActivators[$activatorPost])) {
                /** @var ExtensionConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                if (($appDirPath = helper()->getClassDirectory($activator)) !== false && AppConfig::isAppNameSpace($activator)) {
                    $installedFilePath = $appDirPath . DIRECTORY_SEPARATOR . '.installed';

                    # You can't delete an app that has .installed file
                    if (helper()->fileExists($installedFilePath)) {
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        continue;
                    }
                    # You can't delete an internal module (the AppNameSpace check should have prevented it but 2 check ain't bad)
                    if ($this->isInternalModulePath($appDirPath)) {
                        $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        continue;
                    }

                    # Okay, Things are fine, remove the app
                    try {
                        $activator->onDelete();
                        if (helper()->deleteDirectory($appDirPath)) {
                            $deletedApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        } else {
                            $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                        }
                    } catch (\Exception $exception) {
                        // Log..
                    }
                }
            }
        }

        if (!empty($errorActivatorName)) {
            $errorActivatorName = implode(', ', $errorActivatorName);
            $this->setErrorMessage("An Error Occurred Deleting App: [$errorActivatorName]");
        } elseif (!empty($deletedApp)) {
            apcu_clear_cache();
            $deletedApp = implode(', ', $deletedApp);
            $this->setSucessMessage("[$deletedApp] App Deleted");
            AppConfig::updateRestartService();
            RefreshTreeSystem::RefreshTreeSystem();
            return self::DONE;
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function OnAppUpdateState (): string
    {
        if ($this->permissionOK()) {
            return $this->switchState(self::OnAppProcessUpdateState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function OnAppProcessUpdateState (): string
    {
        $appOrModuleToUpdate = [];
        foreach ($this->activatorsFromPost as $activatorPost) {
            if (isset($this->allActivators[$activatorPost])) {
                /** @var ExtensionConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                if (($appDirPath = helper()->getClassDirectory($activator)) !== false) {

                    # is module
                    if (str_starts_with($appDirPath, AppConfig::getModulesPath())) {
                        $moduleUpdate = new UpdateMechanismState();
                        $moduleUpdate->reset()->setUpdates([helper()->getFileName($appDirPath)])->setTypes(['module'])->setAction('update')
                            ->runStates(false);
                        if ($moduleUpdate->getStateResult() === SimpleState::ERROR) {
                            return self::ERROR;
                        }
                    }

                    # is app
                    if (str_starts_with($appDirPath, AppConfig::getAppsPath())) {
                        $appUpdate = new UpdateMechanismState();
                        $appUpdate->reset()->setUpdates([helper()->getFileName($appDirPath)])->setTypes(['app'])->setAction('update')
                            ->runStates(false);
                        if ($appUpdate->getStateResult() === SimpleState::ERROR) {
                            return self::ERROR;
                        }
                    }

                    $appOrModuleToUpdate[] = helper()->getFileName($appDirPath);
                }
            }
        }

        AppConfig::addUpdateMigrationsJob();
        AppConfig::updateRestartService();
        $appOrModuleToUpdate = implode(', ', $appOrModuleToUpdate);
        $this->setSucessMessage("[$appOrModuleToUpdate] Updated: Reload Page (If Any, Migrations Scheduled)");
        RefreshTreeSystem::RefreshTreeSystem();
        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function OnAppUploadState (): string
    {
        # Checking Only App Permission Since You Can Only Install Inside The App Directory
        if ($this->appPermissionOk()) {
            return $this->switchState(self::OnAppProcessUploadState, self::NEXT);
        }
        return $this->switchState(self::OnAppPermissionErrorState, self::NEXT);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function OnAppProcessUploadState (): string
    {
        $this->appInstallationService->uploadApp($this->getPluginURL(), [
            'AppType'   => 2, // force AppType
            'Signature' => $this->getPluginSignatureHash(),
        ]);

        if ($this->appInstallationService->fails()) {
            $this->setErrorMessage($this->appInstallationService->getErrorsAsString());
            return self::ERROR;
        } else {
            AppConfig::addUpdateMigrationsJob();
            AppConfig::updateRestartService();
            $message = $this->appInstallationService->getMessage();
            $this->setSucessMessage("$message: Go To App List Page, Ignore if on App Page");
            return self::DONE;
        }
    }

    /**
     * @throws \Exception
     */
    public function appPermissionOk (): bool
    {
        return helper()->isReadable(AppConfig::getAppsPath()) && helper()->isWritable(AppConfig::getAppsPath());
    }

    /**
     * @throws \Exception
     */
    public function modulePermissionOk (): bool
    {
        return helper()->isReadable(AppConfig::getModulesPath()) && helper()->isWritable(AppConfig::getModulesPath());
    }

    /**
     * @throws \Exception
     */
    public function permissionOK (): bool
    {
        return helper()->isReadable(AppConfig::getAppsPath()) && helper()->isWritable(AppConfig::getAppsPath())
            && helper()->isReadable(AppConfig::getModulesPath()) && helper()->isWritable(AppConfig::getModulesPath());
    }

    public function isInternalModulePath ($appDir): bool
    {
        return str_starts_with($appDir, AppConfig::getModulesPath());
    }


    /**
     * @return array
     */
    public function getActivatorsFromPost (): array
    {
        return $this->activatorsFromPost;
    }

    /**
     * @param array $activatorsFromPost
     */
    public function setActivatorsFromPost (array $activatorsFromPost): void
    {
        $this->activatorsFromPost = $activatorsFromPost;
    }

    /**
     * @return array
     */
    public function getAllActivators (): array
    {
        return $this->allActivators;
    }

    /**
     * @param array $allActivators
     */
    public function setAllActivators (array $allActivators): void
    {
        $this->allActivators = $allActivators;
    }

    /**
     * @return string
     */
    public function getPluginURL (): string
    {
        return $this->pluginURL;
    }

    /**
     * @param string $pluginURL
     *
     * @return AppsSystem
     */
    public function setPluginURL (string $pluginURL): AppsSystem
    {
        $this->pluginURL = $pluginURL;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMessageDebug (): bool
    {
        return $this->messageDebug;
    }

    /**
     * @param bool $messageDebug
     *
     * @return AppsSystem
     */
    public function setMessageDebug (bool $messageDebug): AppsSystem
    {
        $this->messageDebug = $messageDebug;
        return $this;
    }

    /**
     * @return AppInstallationService|null
     */
    public function getAppInstallationService (): ?AppInstallationService
    {
        return $this->appInstallationService;
    }

    /**
     * @param AppInstallationService|null $appInstallationService
     *
     * @return $this
     */
    public function setAppInstallationService (?AppInstallationService $appInstallationService): AppsSystem
    {
        $this->appInstallationService = $appInstallationService;
        return $this;
    }

    public function getPluginSignatureHash (): string
    {
        return $this->pluginSignatureHash;
    }

    /**
     * @param string $pluginSignatureHash
     *
     * @return $this
     */
    public function setPluginSignatureHash (string $pluginSignatureHash): AppsSystem
    {
        $this->pluginSignatureHash = $pluginSignatureHash;
        return $this;
    }

}