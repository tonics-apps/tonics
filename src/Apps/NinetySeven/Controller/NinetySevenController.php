<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\NinetySeven\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

const TonicsTheme_TonicsNinetySevenSettings = 'TonicsTheme_TonicsNinetySevenSettings';

class NinetySevenController
{
    private ?FieldData $fieldData;

    public function __construct(FieldData $fieldData = null)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function edit(): void
    {
        $fieldItems = $this->getFieldData()->generateFieldWithFieldSlug(
            ['app-ninety-seven-settings'],
            $this->getSettingData()
        )->getHTMLFrag();

        view('Apps::NinetySeven/Views/settings', [
                'FieldItems' => $fieldItems,
            ]
        );
    }

    /**
     * @throws \Exception
     */
    public function update()
    {
        $result = FieldConfig::savePluginFieldSettings(self::getSettingsFile(), $_POST);
        if (!$result){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('ninetySeven.settings'));
        }

        apcu_store(self::getCacheKey(), FieldConfig::loadPluginSettings(self::getSettingsFile()));
        session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('ninetySeven.settings'));
    }

    public static function getSettingsFile(): string
    {
        return AppConfig::getAppsPath() . DIRECTORY_SEPARATOR . 'NinetySeven' . DIRECTORY_SEPARATOR . 'settings.json';
    }

    /**
     * @throws \Exception
     */
    public static function getSettingData()
    {
        $settings = apcu_fetch(self::getCacheKey());
        if ($settings === false){
            $settings = FieldConfig::loadPluginSettings(self::getSettingsFile());
        }

        return $settings;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . TonicsTheme_TonicsNinetySevenSettings;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData(): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData|null $fieldData
     */
    public function setFieldData(?FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }
}