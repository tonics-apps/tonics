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

use App\Modules\Payment\Events\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\AudioTonicsPaymentInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class AudioTonicsPayPalHandler implements HandlerInterface, AudioTonicsPaymentInterface
{
    const Query_ClientCredentials = 'ClientPaymentCredentials';
    const Query_GenerateInvoiceID = 'GenerateInvoiceID';
    const Query_CapturedPaymentDetails = 'CapturedPaymentDetails';

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

    public function getAccessToken() {

        $client_id = '';
        $secret = '';
        $live = 'hhh';
        $url = 'https://api.paypal.com/v1/oauth2/token';

        if (!$live){
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
        return json_decode($response);
    }

    function confirmOrder($accessToken, $orderId) {
        $live = 'hhh';
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