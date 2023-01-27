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

use App\Modules\Core\Library\Authentication\Session;
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
    public static function getAccessToken() {

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
                $expiration_date = time() + $accessInfo->expires_in;
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

    function confirmOrder($accessToken, $orderId) {
        $settings = PaymentSettingsController::getSettingsData();
        $live = false;
        if (key_exists(self::Key_IsLive, $settings) && $settings[self::Key_IsLive] === '1'){
            $live = true;
        }
        $payPalConfirmOrderURL = 'https://api.paypal.com/v2/checkout/orders/';
        if (!$live){
            $payPalConfirmOrderURL = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
        }

        $url = $payPalConfirmOrderURL . $orderId . '/confirm-payment-source';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }


}