<?php

namespace App\Modules\Core\Library;

use App\Library\ModuleRegistrar\Interfaces\ModuleConfig as ModuleConfig;
use App\Library\ModuleRegistrar\Interfaces\PluginConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Authentication\Session;
use JetBrains\PhpStorm\NoReturn;
use ReflectionClass;

class ThemeSystem extends SimpleState
{

    # States For ThemeSystem
    const OnThemeActivateState = 'OnThemeActivateState';
    const OnThemePermissionErrorState = 'OnThemePermissionErrorState';
    const OnThemeCheckCurrentInstalledState = 'OnThemeCheckCurrentInstalledState';
    const OnThemeRemoveCurrentInstalledFileState = 'OnThemeRemoveCurrentInstalledFileState';
    const OnThemeProcessActivationState = 'OnThemeProcessActivationState';
    const OnThemeDuplicateActivationRequestState = 'OnThemeDuplicateActivationRequestState';
    const OnThemeActivatedState = 'OnThemeActivatedState';

    const OnThemeDeActivateState = 'OnThemeDeActivateState';
    const OnThemeProcessDeActivationState = 'OnThemeProcessDeActivationState';
    const OnThemeDeActivatedState = 'OnThemeDeActivatedState';

    private ModuleConfig|PluginConfig $toInstallTheme;
    private ModuleConfig|PluginConfig|null $currentInstalledTheme = null;

    /**
     * @throws \Exception
     */
    public function __construct(ModuleConfig $themeToInstall)
    {
        $theme = helper()->getAppsActivator([ModuleConfig::class], helper()->getAllThemesDirectory());
        if (!empty($theme)){
            $theme = $theme[array_key_first($theme)];
            $this->currentInstalledTheme = $theme;
        }
        $this->toInstallTheme = $themeToInstall;
    }

    /**
     * @throws \Exception
     */
    public function OnThemeActivateState(): string
    {
        if (helper()->isReadable(AppConfig::getThemesPath()) && helper()->isWritable(AppConfig::getThemesPath())){
            $this->switchState(self::OnThemeCheckCurrentInstalledState);
            return self::NEXT;
        }
        $this->switchState(self::OnThemePermissionErrorState);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function OnThemePermissionErrorState()
    {
        session()->flash(['An Error Occurred Reading or Writing File'], []);
        redirect(route('themes.index'));
    }

    /**
     * @throws \Exception
     */
    public function OnThemeCheckCurrentInstalledState(): string
    {
        if ($this->getCurrentInstalledTheme() instanceof ModuleConfig){
            if (get_class($this->getCurrentInstalledTheme()) === get_class($this->getToInstallTheme())){
                $this->switchState(self::OnThemeDuplicateActivationRequestState);
            } else {
                $this->switchState(self::OnThemeRemoveCurrentInstalledFileState);
            }
            return self::NEXT;
        }

        # No Active Theme
        $this->switchState(self::OnThemeProcessActivationState);
        return self::NEXT;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function OnThemeRemoveCurrentInstalledFileState()
    {
        $ref = new ReflectionClass($this->getCurrentInstalledTheme());
        $currentInstalledThemeDir = dirname($ref->getFileName());
        $installedFilePath = $currentInstalledThemeDir . DIRECTORY_SEPARATOR . '.installed';
        if (helper()->fileExists($installedFilePath) && helper()->forceDeleteFile($installedFilePath)){
            $this->switchState(self::OnThemeProcessActivationState);
            return self::NEXT;
        }

        session()->flash(['An Error Occurred De-Activating Current Installed Theme'], []);
        redirect(route('themes.index'));
    }

    /**
     * @throws \Exception
     */
    public function OnThemeProcessActivationState()
    {
        $ref = new ReflectionClass($this->getToInstallTheme());
        $toInstallThemeDir = dirname($ref->getFileName());
        $installedFilePath = $toInstallThemeDir . DIRECTORY_SEPARATOR . '.installed';
        $result = @file_put_contents($installedFilePath, '');
        if ($result !== false){
            $this->switchState(self::OnThemeActivatedState);
            return self::NEXT;
        }

        session()->flash(['An Error Occurred Activating Theme'], []);
        redirect(route('themes.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function OnThemeDuplicateActivationRequestState()
    {
        session()->flash(['Theme Duplicate Activation Error Request'], []);
        redirect(route('themes.index'));
    }

    /**
     * @throws \Exception
     */
    public function OnThemeActivatedState(): string
    {
        if ($this->toInstallTheme instanceof PluginConfig){
            $this->toInstallTheme->onInstall();
        }

        apcu_clear_cache();
        $themeName = (new ReflectionClass($this->getToInstallTheme()))->getShortName();
        session()->flash(["$themeName Theme Installed"], [], type: Session::SessionCategories_FlashMessageSuccess);
        return self::DONE;
    }

    /**
     * @throws \Exception
     */
    public function OnThemeDeActivateState(): string
    {
        if (helper()->isReadable(AppConfig::getThemesPath()) && helper()->isWritable(AppConfig::getThemesPath())){
            $this->switchState(self::OnThemeProcessDeActivationState);
            return self::NEXT;
        }
        $this->switchState(self::OnThemePermissionErrorState);
        return self::NEXT;
    }

    /**
     * @throws \Exception
     */
    public function OnThemeProcessDeActivationState()
    {
        $ref = new ReflectionClass($this->getToInstallTheme());
        $toInstallThemeDir = dirname($ref->getFileName());
        $installedFilePath = $toInstallThemeDir . DIRECTORY_SEPARATOR . '.installed';
        if (helper()->fileExists($installedFilePath) && helper()->forceDeleteFile($installedFilePath)){
            $this->switchState(self::OnThemeDeActivatedState);
            return self::NEXT;
        }

        session()->flash(['An Error Occurred DeActivating Theme'], []);
        redirect(route('themes.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function OnThemeDeActivatedState(): string
    {
        if ($this->toInstallTheme instanceof PluginConfig){
            $this->toInstallTheme->onUninstall();
        }

        apcu_clear_cache();
        $themeName = (new ReflectionClass($this->getToInstallTheme()))->getShortName();
        session()->flash(["$themeName Theme UnInstalled"], [], type: Session::SessionCategories_FlashMessageSuccess);
        return self::DONE;
    }

    /**
     * @return ModuleConfig|PluginConfig
     */
    public function getToInstallTheme(): ModuleConfig|PluginConfig
    {
        return $this->toInstallTheme;
    }

    /**
     * @return mixed
     */
    public function getCurrentInstalledTheme(): mixed
    {
        return $this->currentInstalledTheme;
    }
}