<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Payment\Schedules\AudioTonics\ManuallyConfirmAudioTonicsPaymentPayPal;

class CoreSettingsController
{
    const TonicsModule_TonicsCoreSettings = 'TonicsModule_TonicsCoreSettings';

    const AppSettings_AppName = 'tonics_core_settings_appSettings_appName';
    const AppSettings_AppURL = 'tonics_core_settings_appSettings_appURL';
    const AppSettings_AppTimeZone = 'tonics_core_settings_appSettings_appTimeZone';
    const AppSettings_AppLog404 = 'tonics_core_settings_appSettings_appLog404';
    const AppSettings_AppEnvironment = 'tonics_core_settings_appSettings_appEnvironment';

    const Mail_Mailer = 'tonics_core_settings_mail_mailer';
    const Mail_MailHost = 'tonics_core_settings_mail_mailHost';
    const Mail_MailPort = 'tonics_core_settings_mail_mailPort';
    const Mail_MailUsername = 'tonics_core_settings_mail_mailUsername';
    const Mail_MailPassword = 'tonics_core_settings_mail_mailPassword';
    const Mail_MailEncryption = 'tonics_core_settings_mail_mailEncryption';
    const Mail_MailFromAddress = 'tonics_core_settings_mail_mailFromAddress';
    const Mail_MailReplyTo = 'tonics_core_settings_mail_mailReplyTo';

    const Updates_AutoUpdateModules = 'tonics_core_settings_updates_autoUpdateModules';
    const Updates_AutoUpdateApps = 'tonics_core_settings_updates_autoUpdateApps';

    const MediaDrive_DropBoxRepeaterName = 'tonics_core_settings_mediaDrives_dropBoxRepeater';
    const MediaDrive_DropBoxKey = 'tonics_core_settings_mediaDrives_dropBoxRepeater_Key';
    const MediaDrive_DropBoxName = 'tonics_core_settings_mediaDrives_dropBoxRepeater_Name';


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
        $fieldItems = $this->getFieldData()->generateFieldWithFieldSlug(
            ['core-settings'],
            $this->getSettingsData()
        )->getHTMLFrag();

        view('Modules::Core/Views/settings', [
                'FieldItems' => $fieldItems
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
            redirect(route('admin.core.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('admin.core.settings'));
        }
    }

    /**
     * @throws \Exception
     */
    public static function getSettingsData()
    {
        if (!self::$settings) {
            $settings = apcu_fetch(self::getCacheKey());
            if ($settings === false){
                $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
            }
            self::$settings = $settings;
        }

        return self::$settings;
    }

    /**
     * @param string $key
     * @param $default
     * If $key value is empty, we use $default
     * @return string
     * @throws \Exception
     */
    public static function getSettingsValue(string $key,  $default = null): mixed
    {
        #
        # If DB doesn't exist here, then it means we are accessing settings too early,
        # we fall back to $default
        #
        if (!function_exists('db')){
            return $default;
        }

        $settings = self::getSettingsData();
        if (key_exists($key, $settings)){
            $value = $settings[$key];
            if ($value !== ''){
                return $value;
            }
        }

        return $default;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::TonicsModule_TonicsCoreSettings;
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