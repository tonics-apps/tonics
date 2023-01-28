<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\EventHandlers\TrackPaymentMethods;

use App\Modules\Core\Library\Tables;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Events\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\AudioTonicsPaymentInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class AudioTonicsPayPalHandler implements HandlerInterface, AudioTonicsPaymentInterface
{
    const Query_ClientCredentials = 'ClientPaymentCredentials';
    const Query_GenerateInvoiceID = 'GenerateInvoiceID';
    const Query_CapturedPaymentDetails = 'CapturedPaymentDetails';

    const Key_IsLive = 'tonics_payment_settings_paypal_live';
    const Key_LiveClientID = 'tonics_payment_settings_apiCredentials_Live_ClientID';
    const Key_LiveSecretKey = 'tonics_payment_settings_apiCredentials_Live_SecretKey';

    const Key_SandBoxClientID = 'tonics_payment_settings_apiCredentials_SandBox_ClientID';
    const Key_SandBoxSecretKey = 'tonics_payment_settings_apiCredentials_SandBox_SecretKey';

    const Key_WebHookID = 'tonics_payment_settings_apiCredentials_WebHook_ID';

    const GlobalTableKey = 'AudioTonics_PayPal_AccessToken_Info';

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddTrackPaymentEvent */
        $event->addPaymentHandler($this);
    }

    public function name(): string
    {
        return 'AudioTonicsPayPalHandler';
    }

    /**
     * @throws \Exception
     */
    public function handlePayment(): void
    {

        $queryType = url()->getHeaderByKey('PaymentQueryType');
        if ($queryType === self::Query_GenerateInvoiceID){
            $this->generateInvoiceID();
            return;
        }

        if ($queryType === self::Query_CapturedPaymentDetails){
            $body = url()->getEntityBody();
            $body = json_decode($body);
            dd($body, 'MARKER_THAT', $this->confirmOrder(self::getAccessToken(), $body?->orderData?->id));
            dd($body);
        }

        if ($queryType === self::Query_ClientCredentials){

        }

    }



    /**
     * @throws \Exception
     */
    public function generateInvoiceID()
    {
        response()->onSuccess(uniqid('AudioTonics_', true));
    }

    /**
     * @return null
     * @throws \Exception
     */
    public static function getAccessToken()
    {
        $globalTable = Tables::getTable(Tables::GLOBAL);
        $settings = PaymentSettingsController::getSettingsData();
        $live = false;
        $generateNewToken = true;
        $accessToken = null;
        $expiration_date = time();

        $accessInfo = db(true)->Select('*')
            ->From($globalTable)->WhereEquals('`key`', self::GlobalTableKey)->FetchFirst();
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
            if (key_exists(self::Key_IsLive, $settings) && $settings[self::Key_IsLive] === '1'){
                $live = true;
            }

            $client_id = $settings[self::Key_LiveClientID];
            $secret = $settings[self::Key_LiveSecretKey];
            $url = 'https://api.paypal.com/v1/oauth2/token';

            if (!$live){
                $url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
                $client_id = $settings[self::Key_SandBoxClientID];
                $secret = $settings[self::Key_SandBoxSecretKey];
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
                $accessToken = $response->access_token;
                $expiration_date = time() + $response->expires_in;

                db(true)->insertOnDuplicate(
                    $globalTable,
                    [
                        'key' => self::GlobalTableKey,
                        'value' => json_encode(['access_token' => $accessToken, 'expires_in' => $expiration_date])
                    ],
                    ['value']);
            }
        }

        return $accessToken;
    }

    public function confirmOrder($accessToken, $orderId) {
        $settings = PaymentSettingsController::getSettingsData();
        $live = false;
        if (key_exists(self::Key_IsLive, $settings) && $settings[self::Key_IsLive] === '1'){
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

        $curlGetOrder = curl_init($checkoutOrderURL . $orderId);
        curl_setopt($curlGetOrder, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curlGetOrder, CURLOPT_RETURNTRANSFER, true);
        $responseOfGetOrder = curl_exec($curlGetOrder);
        curl_close($curlGetOrder);
        $responseOfGetOrder = json_decode($responseOfGetOrder);

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
    public static function verifyWebHookSignature($webhookEvent = null): bool
    {
        $settings = PaymentSettingsController::getSettingsData();
        $live = false;
        if (key_exists(self::Key_IsLive, $settings) && $settings[self::Key_IsLive] === '1'){
            $live = true;
        }
        $webhook_verify_url = 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature';
        if ($live){
            $webhook_verify_url = 'https://api.paypal.com/v1/notifications/verify-webhook-signature';
        }
        if (!isset($settings[self::Key_WebHookID])){
            return false;
        }

        $webhook_id = $settings[self::Key_WebHookID];
        $data = [
            "transmission_id" => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
            "transmission_time" => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '',
            "cert_url" => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '',
            "auth_algo" => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
            "transmission_sig" => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
            "webhook_id" => $webhook_id,
            "webhook_event" => $webhookEvent
        ];

        db(true)->insertOnDuplicate(
            Tables::getTable(Tables::GLOBAL),
            [
                'key' => 'WebHook_Data',
                'value' => json_encode($data)
            ],
            ['value']);

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

            db(true)->insertOnDuplicate(
                Tables::getTable(Tables::GLOBAL),
                [
                    'key' => 'WebHook_Verification',
                    'value' => json_encode($result)
                ],
                ['value']);

            if (isset($result['verification_status'])){
                return $result['verification_status'] === 'SUCCESS';
            }
        }

        return false;
    }



}