<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsSeo\Controller;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

class TonicsSeoController
{
    private ?FieldData $fieldData;

    const CACHE_KEY = 'TonicsPlugin_TonicsSEOSettings';

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
            ['app-tonicsseo-settings'],
            $this->getSettingsData()
        )->getHTMLFrag();

        view('Apps::TonicsSeo/Views/settings', [
                'FieldItems' => $fieldItems,
            ]
        );
    }


    /**
     * @throws \Exception
     */
    public function update()
    {
        try {
            $settings = FieldConfig::savePluginFieldSettings(self::getCacheKey(), $_POST);
            apcu_store(self::getCacheKey(), $settings);
            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('tonicsSeo.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsSeo.settings'));
        }
    }

    public function sitemap()
    {

    }

    /**
     * @throws \Exception
     */
    public static function getSettingsData()
    {
        $settings = apcu_fetch(self::getCacheKey());
        if ($settings === false){
            $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
        }

        return $settings ?? [];
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::CACHE_KEY;
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