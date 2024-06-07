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

namespace App\Modules\Payment\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Field\Data\FieldData;
use Throwable;

class PaymentSettingsController
{
    const TonicsSolution_AudioTonics    = 'AudioTonics';
    const TonicsSolution_TonicsCloud    = 'TonicsCloud';
    const TonicsSolution_TonicsAppStore = 'TonicsAppStore';
    const TonicsSolution_TonicsCommerce = 'TonicsCommerce'; // not yet available
    const GlobalTableKey                = 'AudioTonics_PayPal_AccessToken_Info';

    // For FlutterWave
    const FlutterWave_Enabled           = 'tonics_payment_settings_flutterWave_enabled';
    const FlutterWave_Key_IsLive        = 'tonics_payment_settings_flutterWave_live';
    const FlutterWave_Key_LivePublicKey = 'tonics_payment_settings_flutterWave_apiCredentials_Live_PublicKey';
    const FlutterWave_Key_LiveSecretKey = 'tonics_payment_settings_flutterWave_apiCredentials_Live_SecretKey';

    const FlutterWave_Key_SandBoxPublicKey = 'tonics_payment_settings_flutterWave_apiCredentials_SandBox_PublicKey';
    const FlutterWave_Key_SandBoxSecretKey = 'tonics_payment_settings_flutterWave_apiCredentials_SandBox_SecretKey';

    // For PayStack
    const PayStack_Enabled           = 'tonics_payment_settings_paystack_enabled';
    const PayStack_Key_IsLive        = 'tonics_payment_settings_paystack_live';
    const PayStack_Key_LivePublicKey = 'tonics_payment_settings_paystack_apiCredentials_Live_PublicKey';
    const PayStack_Key_LiveSecretKey = 'tonics_payment_settings_paystack_apiCredentials_Live_SecretKey';

    const PayStack_Key_SandBoxPublicKey = 'tonics_payment_settings_paystack_apiCredentials_SandBox_PublicKey';
    const PayStack_Key_SandBoxSecretKey = 'tonics_payment_settings_paystack_apiCredentials_SandBox_SecretKey';

    // For PayPal...
    const PayPal_Enabled           = 'tonics_payment_settings_paypal_enabled';
    const PayPal_Key_IsLive        = 'tonics_payment_settings_paypal_live';
    const PayPal_Key_LiveClientID  = 'tonics_payment_settings_apiCredentials_Live_ClientID';
    const PayPal_Key_LiveSecretKey = 'tonics_payment_settings_apiCredentials_Live_SecretKey';

    const PayPal_Key_SandBoxClientID  = 'tonics_payment_settings_apiCredentials_SandBox_ClientID';
    const PayPal_Key_SandBoxSecretKey = 'tonics_payment_settings_apiCredentials_SandBox_SecretKey';

    const PayPal_Key_WebHookID               = 'tonics_payment_settings_apiCredentials_WebHook_ID';
    const TonicsModule_TonicsPaymentSettings = 'TonicsModule_TonicsPaymentSettings';
    private ?FieldData $fieldData;

    public function __construct (FieldData $fieldData = null)
    {
        $this->fieldData = $fieldData;
    }

    /**
     * @throws \Exception|Throwable
     */
    public function edit (): void
    {
        $fieldItems = $this->getFieldData()->generateFieldWithFieldSlug(
            ['payment-settings'],
            $this->getSettingsData(),
        )->getHTMLFrag();

        view('Modules::Payment/Views/settings', [
            'FieldItems' => $fieldItems,
        ],
        );
    }

    /**
     * @throws \Exception|Throwable
     */
    public function update ()
    {
        try {
            $settings = FieldConfig::savePluginFieldSettings(self::getCacheKey(), $_POST);
            apcu_store(self::getCacheKey(), $settings);
            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('payment.settings'));
        } catch (\Exception) {
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('payment.settings'));
        }
    }

    /**
     * @throws \Exception
     */
    public static function getSettingsData ()
    {
        $settings = apcu_fetch(self::getCacheKey());
        if ($settings === false) {
            $settings = FieldConfig::loadPluginSettings(self::getCacheKey());
        }
        return $settings;
    }

    /**
     * @param string $key
     * @param array|null $data
     * Add the data or I default to getSettingsData()
     *
     * @return bool
     * @throws \Exception
     */
    public static function isEnabled (string $key, array $data = null): bool
    {
        if ($data) {
            $settingsData = $data;
        } else {
            $settingsData = self::getSettingsData();
        }

        if (isset($settingsData[$key])) {
            return $settingsData[$key] === '1';
        }

        return false;
    }

    public static function getCacheKey (): string
    {
        return AppConfig::getAppCacheKey() . self::TonicsModule_TonicsPaymentSettings;
    }

    /**
     * @return FieldData|null
     */
    public function getFieldData (): ?FieldData
    {
        return $this->fieldData;
    }

    /**
     * @param FieldData|null $fieldData
     */
    public function setFieldData (?FieldData $fieldData): void
    {
        $this->fieldData = $fieldData;
    }
}