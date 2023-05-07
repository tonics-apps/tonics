<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Controllers;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\FieldConfig;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
use App\Modules\Field\Data\FieldData;
use App\Modules\Payment\Schedules\AudioTonics\ManuallyConfirmAudioTonicsPaymentPayPal;

class PaymentSettingsController
{
    const TonicsSolution_AudioTonics = 'AudioTonics';
    const TonicsSolution_TonicsCommerce = 'TonicsCommerce'; // not yet available
    const GlobalTableKey = 'AudioTonics_PayPal_AccessToken_Info';

    // For FlutterWave
    const FlutterWave_Enabled = 'tonics_payment_settings_flutterWave_enabled';
    const FlutterWave_Key_IsLive = 'tonics_payment_settings_flutterWave_live';
    const FlutterWave_Key_LivePublicKey = 'tonics_payment_settings_flutterWave_apiCredentials_Live_PublicKey';
    const FlutterWave_Key_LiveSecretKey = 'tonics_payment_settings_flutterWave_apiCredentials_Live_SecretKey';

    const FlutterWave_Key_SandBoxPublicKey = 'tonics_payment_settings_flutterWave_apiCredentials_SandBox_PublicKey';
    const FlutterWave_Key_SandBoxSecretKey = 'tonics_payment_settings_flutterWave_apiCredentials_SandBox_SecretKey';

    // For PayPal...
    const PayPal_Enabled = 'tonics_payment_settings_paypal_enabled';
    const PayPal_Key_IsLive = 'tonics_payment_settings_paypal_live';
    const PayPal_Key_LiveClientID = 'tonics_payment_settings_apiCredentials_Live_ClientID';
    const PayPal_Key_LiveSecretKey = 'tonics_payment_settings_apiCredentials_Live_SecretKey';

    const PayPal_Key_SandBoxClientID = 'tonics_payment_settings_apiCredentials_SandBox_ClientID';
    const PayPal_Key_SandBoxSecretKey = 'tonics_payment_settings_apiCredentials_SandBox_SecretKey';

    const PayPal_Key_WebHookID = 'tonics_payment_settings_apiCredentials_WebHook_ID';

    private ?FieldData $fieldData;

    const TonicsModule_TonicsPaymentSettings = 'TonicsModule_TonicsPaymentSettings';

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
            ['payment-settings'],
            $this->getSettingsData()
        )->getHTMLFrag();

        view('Modules::Payment/Views/settings', [
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

            # Enqueue ManuallyConfirmAudioTonicsPaymentPayPal for Schedule
            $ManuallyConfirmAudioTonicsPaymentPayPal = new ManuallyConfirmAudioTonicsPaymentPayPal();
            schedule()->enqueue($ManuallyConfirmAudioTonicsPaymentPayPal);

            session()->flash(['Settings Updated'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('payment.settings'));
        }catch (\Exception){
            session()->flash(['An Error Occurred Saving Settings'], $_POST);
            redirect(route('payment.settings'));
        }
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
        return $settings;
    }

    /**
     * @param string $key
     * @param array|null $data
     * Add the data or I default to getSettingsData()
     * @return bool
     * @throws \Exception
     */
    public static function isPaymentEnabled(string $key, array $data = null): bool
    {
        if ($data){
            $settingsData = $data;
        } else {
            $settingsData = self::getSettingsData();
        }

        if (isset($settingsData[$key])){
            return $settingsData[$key] === '1';
        }

        return false;
    }

    public static function getCacheKey(): string
    {
        return AppConfig::getAppCacheKey() . self::TonicsModule_TonicsPaymentSettings;
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

    /**
     * @return null
     * @throws \Exception
     */
    public static function getAccessToken()
    {
        $globalTable = Tables::getTable(Tables::GLOBAL);
        $settings = self::getSettingsData();
        $live = false;
        $generateNewToken = true;
        $accessToken = null;
        $expiration_date = time();

        $accessInfo = null;

        db(onGetDB: function ($db) use ($globalTable, &$accessInfo){
            $accessInfo = $db->Select('*')
                ->From($globalTable)->WhereEquals('`key`', self::GlobalTableKey)->FetchFirst();
        });

        if (isset($accessInfo->value) && !empty($accessInfo->value)) {
            $accessInfo = json_decode($accessInfo->value);
            if (isset($accessInfo->access_token) && isset($accessInfo->expires_in)){
                $accessToken = $accessInfo->access_token;
                $expiration_date = $accessInfo->expires_in;
                $generateNewToken = false;
            }
        }

        // Before making an API call, check if the token has expired
        if (time() > $expiration_date) {
            // Request a new token
            $generateNewToken = true;
        }

        if ($generateNewToken){
            if (key_exists(self::PayPal_Key_IsLive, $settings) && $settings[self::PayPal_Key_IsLive] === '1'){
                $live = true;
            }

            $client_id = $settings[self::PayPal_Key_LiveClientID];
            $secret = $settings[self::PayPal_Key_LiveSecretKey];
            $url = 'https://api.paypal.com/v1/oauth2/token';

            if (!$live){
                $url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
                $client_id = $settings[self::PayPal_Key_SandBoxClientID];
                $secret = $settings[self::PayPal_Key_SandBoxSecretKey];
            }

            $headers = [
                'Accept: application/json',
                'Accept-Language: en_US',
            ];
            $data = [
                'grant_type' => 'client_credentials'
            ];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERPWD, $client_id . ':' . $secret);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);
            if (isset($response->access_token) && isset($response->expires_in)){
                db(onGetDB: function ($db) use ($globalTable, $response) {
                    $accessToken = $response->access_token;
                    $expiration_date = time() + $response->expires_in;
                    $db->insertOnDuplicate(
                        $globalTable,
                        [
                            'key' => self::GlobalTableKey,
                            'value' => json_encode(['access_token' => $accessToken, 'expires_in' => $expiration_date])
                        ],
                        ['value']);
                });
            }
        }

        return $accessToken;
    }

    /**
     * @throws \Exception
     */
    public static function confirmOrder($accessToken, $orderId) {
        $settings = self::getSettingsData();
        $live = false;
        if (key_exists(self::PayPal_Key_IsLive, $settings) && $settings[self::PayPal_Key_IsLive] === '1'){
            $live = true;
        }
        $checkoutOrderURL = 'https://api.paypal.com/v2/checkout/orders/';
        if (!$live){
            $checkoutOrderURL = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
        }

        $url = $checkoutOrderURL . $orderId . '/confirm-payment-source';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];

        $responseOfGetOrder = self::getOrderDetails($accessToken, $orderId);

        $response = [];
        if (isset($responseOfGetOrder->payment_source)){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['payment_source' => $responseOfGetOrder->payment_source]));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);
        }

        return json_decode($response, true);
    }

    /**
     * @param $accessToken
     * @param $orderID
     * @return mixed
     * @throws \Exception
     */
    public static function getOrderDetails($accessToken, $orderID): mixed
    {
        $settings = self::getSettingsData();
        $live = false;
        if (key_exists(self::PayPal_Key_IsLive, $settings) && $settings[self::PayPal_Key_IsLive] === '1'){
            $live = true;
        }
        $checkoutOrderURL = 'https://api.paypal.com/v2/checkout/orders/';
        if (!$live){
            $checkoutOrderURL = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];

        $curlGetOrder = curl_init($checkoutOrderURL . $orderID);
        curl_setopt($curlGetOrder, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlGetOrder, CURLOPT_RETURNTRANSFER, true);
        $responseOfGetOrder = curl_exec($curlGetOrder);
        curl_close($curlGetOrder);
        return json_decode($responseOfGetOrder);
    }

    /**
     * @throws \Exception
     */
    public static function verifyWebHookSignature($webhookEvent = null): bool
    {
        $settings = self::getSettingsData();
        $live = false;
        if (key_exists(self::PayPal_Key_IsLive, $settings) && $settings[self::PayPal_Key_IsLive] === '1'){
            $live = true;
        }
        $webhook_verify_url = 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature';
        if ($live){
            $webhook_verify_url = 'https://api.paypal.com/v1/notifications/verify-webhook-signature';
        }
        if (!isset($settings[self::PayPal_Key_WebHookID])){
            return false;
        }

        $webhook_id = $settings[self::PayPal_Key_WebHookID];
        $data = [
            "transmission_id" => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
            "transmission_time" => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '',
            "cert_url" => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '',
            "auth_algo" => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
            "transmission_sig" => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
            "webhook_id" => $webhook_id,
            "webhook_event" => $webhookEvent
        ];

        db(onGetDB: function ($db) use ($data){
            $db->insertOnDuplicate(
                Tables::getTable(Tables::GLOBAL),
                [
                    'key' => 'WebHook_Data_' . helper()->randomString(5),
                    'value' => json_encode($data)
                ],
                ['value']);
        });

        $data_string = json_encode($data);
        $access_token = self::getAccessToken();
        $ch = curl_init($webhook_verify_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status == 200) {
            $result = json_decode($result, true);
            db(onGetDB: function ($db) use ($result){
                $db->insertOnDuplicate(
                    Tables::getTable(Tables::GLOBAL),
                    [
                        'key' => 'WebHook_Verification'  . helper()->randomString(5),
                        'value' => json_encode($result)
                    ],
                    ['value']);

            });

            if (isset($result['verification_status'])){
                return $result['verification_status'] === 'SUCCESS';
            }
        }

        return false;
    }
}