<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Core\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;

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
     * @throws \Throwable
     */
    public function edit(): void
    {
        view('Modules::Core/Views/settings', [
                'FieldItems' => FieldConfig::getSettingsHTMLFrag($this->getFieldData(), self::getSettingsData(), ['core-settings'])
            ]
        );
    }

    /**
     * @return void
     * @throws \Throwable
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