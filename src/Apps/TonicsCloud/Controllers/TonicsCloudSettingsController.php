<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

class TonicsCloudSettingsController
{
    const TonicsApp_TonicsCloudSettings = 'TonicsApp_TonicsCloudSettings';

    const LinodeAPIToken = 'tonics_cloud_main_container_APITokens_LinodeAkamai_Key';
    const AWSAPIToken = 'tonics_cloud_main_container_APITokens_AWS_Key';
    const CloudServerIntegrationType = 'tonics_cloud_main_container_cloudServer_Integration';
    const DNSIntegrationType = 'tonics_cloud_main_container_DNS_Integration';

    const LXDTrustPassword = 'tonics_cloud_main_container_LXD_TrustPassword';

    private ?FieldData $fieldData;
    private static array $settings = [];
    public function __construct(FieldData $fieldData = null)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception
     */
    public function edit(): void
    {
        view('Apps::TonicsCloud/Views/settings', [
                'FieldItems' => FieldConfig::getSettingsHTMLFrag($this->getFieldData(), self::getSettingsData(), ['app-tonicscloud-settings'])
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
            redirect(route('tonicsCloud.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('tonicsCloud.settings'));
        }
    }


    /**
     * If key is given, we check if it exists, return the value, otherwise, we return all settings data
     * @param string $key
     * @return array|false|mixed
     * @throws \Exception
     */
    public static function getSettingsData(string $key = ''): mixed
    {
        if (!self::$settings) {
            $settings = apcu_fetch(self::getCacheKey());
            if ($settings === false){
                $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
            }
            self::$settings = $settings;
        }

        if (key_exists($key, self::$settings)){
            return self::$settings[$key];
        }

        return self::$settings;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::TonicsApp_TonicsCloudSettings;
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