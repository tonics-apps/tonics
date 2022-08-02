<?php

namespace App\Modules\Core\States;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use JetBrains\PhpStorm\NoReturn;

class AppsSystem extends SimpleState
{

    private array $activatorsFromPost;
    private array $allActivators;

    const OnAppActivateState = 'OnAppActivateState';
    const OnAppPermissionErrorState = 'OnAppPermissionErrorState';
    const OnAppProcessActivationState = 'OnAppProcessActivationState';

    const OnAppDeActivateState = 'OnAppDeActivateState';
    const OnAppProcessDeActivationState = 'OnAppProcessDeActivationState';

    const OnAppDeleteState = 'OnAppDeleteState';
    const OnAppProcessDeletionState = 'OnAppProcessDeletionState';

    const OnAppUpdateState = 'OnAppUpdateState';
    const OnAppProcessUpdateState = 'OnAppProcessUpdateState';

    /**
     * @throws \Exception
     */
    public function __construct($activatorsFromPost = [])
    {
        $this->allActivators = [
            ...helper()->getAppsActivator([ModuleConfig::class, PluginConfig::class], installed: false),
            ...helper()->getAppsActivator([ModuleConfig::class, PluginConfig::class], helper()->getAllModulesDirectory(), false)
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
        $result = false; $errorActivatorName = []; $installedApp = [];
        foreach ($this->activatorsFromPost as $activatorPost){
            if (isset($this->allActivators[$activatorPost])){
                /** @var PluginConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                $ref = new \ReflectionClass($activator);
                $toInstallThemeDir = dirname($ref->getFileName());
                $installedFilePath = $toInstallThemeDir . DIRECTORY_SEPARATOR . '.installed';
                $result = @file_put_contents($installedFilePath, '');
                if ($result === false){
                    $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                } else {
                    $activator->onInstall();
                    $installedApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
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
                /** @var PluginConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                $ref = new \ReflectionClass($activator);
                $toInstallThemeDir = dirname($ref->getFileName());
                $installedFilePath = $toInstallThemeDir . DIRECTORY_SEPARATOR . '.installed';
                if (helper()->fileExists($installedFilePath) && helper()->forceDeleteFile($installedFilePath)){
                    $activator->onUninstall();
                    $unInstalledApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                } else {
                    $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
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
                /** @var PluginConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                $ref = new \ReflectionClass($activator);
                $toInstallThemeDir = dirname($ref->getFileName());
                $installedFilePath = $toInstallThemeDir . DIRECTORY_SEPARATOR . '.installed';

                # You can't delete an app that has .installed file
                if (helper()->fileExists($installedFilePath)){
                    $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    continue;
                }

                # You can't delete an internal module even with a trick
                if (str_starts_with($toInstallThemeDir, AppConfig::getModulesPath())){
                    $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                    continue;
                }

                # Okay, Things are fine, remove the app
                if(helper()->deleteDirectory($toInstallThemeDir)){
                    $deletedApp[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                } else {
                    $errorActivatorName[] = isset($activator->info()['name']) ? $activator->info()['name'] : '';
                }
            }
        }

        if (!empty($errorActivatorName)){
            $errorActivatorName = implode(',', $errorActivatorName);
            session()->flash(["An Error Occurred Deleting App: [$errorActivatorName]"], []);
            redirect(route('apps.index'));
        }

        if (!empty($deletedApp)){
            $deletedApp = implode(',', $deletedApp);
            session()->flash(["[$deletedApp] App Deleted"], [], type: Session::SessionCategories_FlashMessageSuccess);
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
        $errorActivatorName = []; $updateApp = []; $updateTypes = []; $appOrModuleToUpdate = [];
        foreach ($this->activatorsFromPost as $activatorPost) {
            if (isset($this->allActivators[$activatorPost])) {
                /** @var PluginConfig $activator */
                $activator = $this->allActivators[$activatorPost];
                $ref = new \ReflectionClass($activator);
                $toInstallThemeDir = dirname($ref->getFileName());

                if (str_starts_with($toInstallThemeDir, AppConfig::getModulesPath())){
                    $updateTypes[] = 'module';
                }
                if (str_starts_with($toInstallThemeDir, AppConfig::getAppsPath())){
                    $updateTypes[] = 'app';
                }

                $appOrModuleToUpdate[] = helper()->getFileName($toInstallThemeDir);
            }
        }

        # This is to prevent updating App into Modules Directory, and Vice Versa,
        # So, only One Type is Allowed
        if (count($updateTypes) > 1){
            session()->flash(["Update Type Mismatch"], []);
            redirect(route('apps.index'));
        }

        if (count($updateTypes) === 1){
            helper()->addEventStreamHeader(1000000, 'text/html');
            $updateMechanismState = new UpdateMechanismState();
            $updateMechanismState->reset()->setUpdates($appOrModuleToUpdate)->setTypes($updateTypes)->setAction('update')
                ->runStates(false);
            if ($updateMechanismState->getStateResult() === SimpleState::DONE){
                $appOrModuleToUpdate = implode(',', $appOrModuleToUpdate);
                $this->setSucessMessage("[$appOrModuleToUpdate][$updateTypes[0]] Updated");
                return self::DONE;
            }
        }
        return self::ERROR;

    }

    /**
     * @throws \Exception
     */
    public function permissionOK(): bool
    {
        return helper()->isReadable(AppConfig::getAppsPath()) && helper()->isWritable(AppConfig::getAppsPath())
            && helper()->isReadable(AppConfig::getModulesPath()) && helper()->isWritable(AppConfig::getModulesPath());
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

}