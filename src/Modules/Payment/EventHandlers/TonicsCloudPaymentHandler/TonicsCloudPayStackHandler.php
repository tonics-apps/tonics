<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\EventHandlers\TonicsCloudPaymentHandler;

use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Events\AudioTonics\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\TonicsPaymentInterface;
use App\Modules\Payment\Jobs\TonicsCloud\TonicsCloudConfirmFlutterWavePayment;
use App\Modules\Payment\Jobs\TonicsCloud\TonicsCloudConfirmPayStackPayment;
use App\Modules\Payment\Library\Helper;
use App\Modules\Payment\Library\TonicsCloudHelper;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsCloudPayStackHandler implements HandlerInterface, TonicsPaymentInterface
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
        return 'TonicsCloudPayStackHandler';
    }

    /**
     * @throws \Exception
     */
    public function enabled(): bool
    {
        return PaymentSettingsController::isEnabled(PaymentSettingsController::PayStack_Enabled);
    }


    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function handlePayment(): void
    {
        $queryType = url()->getHeaderByKey('PaymentQueryType');
        if ($queryType === self::Query_GenerateInvoiceID) {
            $this->generateInvoiceID();
            return;
        }

        if ($queryType === self::Query_ClientCredentials) {
            response()->onSuccess(Helper::PayStackPublicKey());
        }

        if ($queryType === self::Query_CapturedPaymentDetails) {
            try {
                $body = url()->getEntityBody();
                $body = json_decode($body);
                $amount = $body->amount ?? 0;
                $data = TonicsCloudHelper::CapturePaymentDetails([
                    'fk_customer_id' => session()::getUserID(),
                    'total_price' => $amount,
                    'others' => json_encode([
                        'payment_email_address' => (isset($body->checkout_email)) ? $body->checkout_email : '',
                        'invoice_id' => $body->invoice_id,
                        'tx_ref' => $body->orderData->trxref,
                        'paystack_trans' => $body->orderData->trans,
                        'payment_method' => 'TonicsPayStack',
                        'payment_amount' => $amount,
                        'payment_multiplier' => 100,
                        'tonics_solution' => PaymentSettingsController::TonicsSolution_TonicsCloud
                    ]),
                ]);

                if (isset($data['PURCHASE_RECORD'])){
                    $confirmPayStackPayment = new TonicsCloudConfirmPayStackPayment();
                    $confirmPayStackPayment->setData($data['PURCHASE_RECORD']);
                    job()->enqueue($confirmPayStackPayment);
                }

                response()->onSuccess('', $data['MESSAGE']);
            } catch (\Exception $exception) {
                response()->onError(400, $exception->getMessage());
                // Log..
            } catch (\Throwable $exception) {
                response()->onError(400, $exception->getMessage());
                // Log..
            }
        }
    }

    /**
     * @param $secretKey
     * @param $id
     * @return mixed
     */
    public static function getPayStackOrderDetails($secretKey, $id): mixed
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
     * @throws \Exception|\Throwable
     */
    public function generateInvoiceID(): void
    {
        response()->onSuccess(uniqid('TonicsCloud_', true));
    }
}