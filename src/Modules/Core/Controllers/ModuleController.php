<?php

namespace App\Modules\Core\Controllers;

use App\InitLoader;
use App\Library\ModuleRegistrar\Interfaces\ModuleConfig as ModuleConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\ModuleData;

class ModuleController
{
    private ModuleData $moduleData;

    /**
     * @param ModuleData $moduleData
     */
    public function __construct(ModuleData $moduleData)
    {
        $this->moduleData = $moduleData;
    }

    /**
     * @throws \Exception
     */
    public function index(): void
    {
        $modules = helper()->getModuleActivators([ModuleConfig::class]);
        view('Modules::Core/Views/Theme/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'ThemeListing' => $this->getModuleData()->adminModuleListing($modules),
        ]);
        dd($modules, InitLoader::getAllThemes());
    }

    /**
     * @return ModuleData
     */
    public function getModuleData(): ModuleData
    {
        return $this->moduleData;
    }

    /**
     * @param ModuleData $moduleData
     */
    public function setModuleData(ModuleData $moduleData): void
    {
        $this->moduleData = $moduleData;
    }


}