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
        if (helper()->isReadable(AppConfig::getAppsPath()) && helper()->isWritable(AppConfig::getAppsPath())){
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
        if (helper()->isReadable(AppConfig::getAppsPath()) && helper()->isWritable(AppConfig::getAppsPath())){
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