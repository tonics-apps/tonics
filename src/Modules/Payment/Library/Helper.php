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

namespace App\Modules\Payment\Library;

use App\Modules\Core\Library\Tables;
use App\Modules\Payment\Controllers\PaymentSettingsController;

class Helper
{
    /**
     * @param $accessToken
     * @param $orderID
     * @return mixed
     * @throws \Exception
     */
    public static function PayPalOrderDetails($accessToken, $orderID): mixed
    {
        if (PaymentSettingsController::isEnabled(PaymentSettingsController::PayPal_Key_IsLive)){
            $checkoutOrderURL = 'https://api.paypal.com/v2/checkout/orders/';
        } else {
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
     * @return string
     * @throws \Exception
     */
    public static function PayPalPublicKey(): string
    {
        $settings = PaymentSettingsController::getSettingsData();
        if (PaymentSettingsController::isEnabled(PaymentSettingsController::PayPal_Key_IsLive)) {
            $credentials = $settings[PaymentSettingsController::PayPal_Key_LiveClientID] ?? '';
        } else {
            $credentials = $settings[PaymentSettingsController::PayPal_Key_SandBoxClientID] ?? '';
        }
        return $credentials;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function PayPalSecretKey(): string
    {
        $settings = PaymentSettingsController::getSettingsData();
        if (PaymentSettingsController::isEnabled(PaymentSettingsController::PayPal_Key_IsLive)) {
            $credentials = $settings[PaymentSettingsController::PayPal_Key_LiveSecretKey] ?? '';
        } else {
            $credentials = $settings[PaymentSettingsController::PayPal_Key_SandBoxSecretKey] ?? '';
        }
        return $credentials;
    }

    /**
     * @throws \Exception
     */
    public static function PayPalAccessToken()
    {
        $globalTable = Tables::getTable(Tables::GLOBAL);
        $generateNewToken = true;
        $accessToken = null;
        $expiration_date = time();

        $accessInfo = null;

        db(onGetDB: function ($db) use ($globalTable, &$accessInfo){
            $accessInfo = $db->Select('*')
                ->From($globalTable)->WhereEquals('`key`', PaymentSettingsController::GlobalTableKey)->FetchFirst();
        });

        if (!empty($accessInfo->value)) {
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
            $client_id = Helper::PayPalPublicKey();
            $secret = Helper::PayPalSecretKey();
            if (PaymentSettingsController::isEnabled(PaymentSettingsController::PayPal_Key_IsLive)){
                $url = 'https://api.paypal.com/v1/oauth2/token';
            } else {
                $url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
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
                            'key' => PaymentSettingsController::GlobalTableKey,
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
    public static function PayPalConfirmOrder($accessToken, $orderId) {

        if (PaymentSettingsController::isEnabled(PaymentSettingsController::PayPal_Key_IsLive)){
            $checkoutOrderURL = 'https://api.paypal.com/v2/checkout/orders/';
        } else {
            $checkoutOrderURL = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
        }

        $url = $checkoutOrderURL . $orderId . '/confirm-payment-source';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];

        $responseOfGetOrder = self::PayPalOrderDetails($accessToken, $orderId);

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
     * @throws \Exception
     */
    public static function PayPalVerifyWebHookSignature($webhookEvent = null): bool
    {
        $settings = PaymentSettingsController::getSettingsData();
        $live = false;
        if (key_exists(PaymentSettingsController::PayPal_Key_IsLive, $settings) && $settings[PaymentSettingsController::PayPal_Key_IsLive] === '1'){
            $live = true;
        }
        $webhook_verify_url = 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature';
        if ($live){
            $webhook_verify_url = 'https://api.paypal.com/v1/notifications/verify-webhook-signature';
        }
        if (!isset($settings[PaymentSettingsController::PayPal_Key_WebHookID])){
            return false;
        }

        $webhook_id = $settings[PaymentSettingsController::PayPal_Key_WebHookID];
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
        $access_token = self::PayPalAccessToken();
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

    /**
     * @param $secretKey
     * @param $id
     * @return mixed
     */
    public static function FlutterWaveOrderDetails($secretKey, $id): mixed
    {
        $endPoint = "https://api.flutterwave.com/v3/transactions/$id/verify";
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $secretKey,
        ];

        $curlGetOrder = curl_init($endPoint);
        curl_setopt($curlGetOrder, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlGetOrder, CURLOPT_RETURNTRANSFER, true);
        $responseOfGetOrder = curl_exec($curlGetOrder);
        curl_close($curlGetOrder);
        return json_decode($responseOfGetOrder);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function FlutterWaveSecretKey(): string
    {
        $settings = PaymentSettingsController::getSettingsData();
        if (PaymentSettingsController::isEnabled(PaymentSettingsController::FlutterWave_Key_IsLive)) {
            $credentials = $settings[PaymentSettingsController::FlutterWave_Key_LiveSecretKey] ?? '';
        } else {
            $credentials = $settings[PaymentSettingsController::FlutterWave_Key_SandBoxSecretKey] ?? '';
        }
        return $credentials;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function FlutterWavePublicKey(): string
    {
        $settings = PaymentSettingsController::getSettingsData();
        if (PaymentSettingsController::isEnabled(PaymentSettingsController::FlutterWave_Key_IsLive)) {
            $credentials = $settings[PaymentSettingsController::FlutterWave_Key_LivePublicKey] ?? '';
        } else {
            $credentials = $settings[PaymentSettingsController::FlutterWave_Key_SandBoxPublicKey] ?? '';
        }
        return $credentials;
    }

    /**
     * @param $secretKey
     * @param $id
     * @return mixed
     */
    public static function PayStackOrderDetails($secretKey, $reference): mixed
    {
        $endPoint = "https://api.paystack.co/transaction/verify/$reference";
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $secretKey,
        ];

        $curlGetOrder = curl_init($endPoint);
        curl_setopt($curlGetOrder, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlGetOrder, CURLOPT_RETURNTRANSFER, true);
        $responseOfGetOrder = curl_exec($curlGetOrder);
        curl_close($curlGetOrder);
        return json_decode($responseOfGetOrder);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function PayStackSecretKey(): string
    {
        $settings = PaymentSettingsController::getSettingsData();
        if (PaymentSettingsController::isEnabled(PaymentSettingsController::PayStack_Key_IsLive)) {
            $credentials = $settings[PaymentSettingsController::PayStack_Key_LiveSecretKey] ?? '';
        } else {
            $credentials = $settings[PaymentSettingsController::PayStack_Key_SandBoxSecretKey] ?? '';
        }
        return $credentials;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function PayStackPublicKey(): string
    {
        $settings = PaymentSettingsController::getSettingsData();
        if (PaymentSettingsController::isEnabled(PaymentSettingsController::PayStack_Key_IsLive)) {
            $credentials = $settings[PaymentSettingsController::PayStack_Key_LivePublicKey] ?? '';
        } else {
            $credentials = $settings[PaymentSettingsController::PayStack_Key_SandBoxPublicKey] ?? '';
        }
        return $credentials;
    }
}