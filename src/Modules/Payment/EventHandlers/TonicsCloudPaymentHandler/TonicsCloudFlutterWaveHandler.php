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

use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Events\AudioTonics\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\TonicsPaymentInterface;
use App\Modules\Payment\Jobs\TonicsCloud\TonicsCloudConfirmFlutterWavePayment;
use App\Modules\Payment\Library\Helper;
use App\Modules\Payment\Library\TonicsCloudHelper;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsCloudFlutterWaveHandler implements HandlerInterface, TonicsPaymentInterface
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
        return 'TonicsCloudFlutterWaveHandler';
    }

    /**
     * @throws \Exception
     */
    public function enabled(): bool
    {
        return PaymentSettingsController::isEnabled(PaymentSettingsController::FlutterWave_Enabled);
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
            response()->onSuccess(Helper::FlutterWavePublicKey());
        }

        if ($queryType === self::Query_CapturedPaymentDetails) {
            try {
                $body = url()->getEntityBody();
                $body = json_decode($body);
                $data = TonicsCloudHelper::CapturePaymentDetails([
                    'fk_customer_id' => session()::getUserID(),
                    'total_price' => $body->orderData->amount ?? 0,
                    'others' => json_encode([
                        'payment_email_address' => (isset($body->orderData->customer->email)) ? $body->orderData->customer->email : '',
                        'invoice_id' => $body->invoice_id,
                        'transaction_id' => $body->orderData->transaction_id,
                        'tx_ref' => $body->orderData->tx_ref,
                        'flw_ref' => $body->orderData->flw_ref,
                        'payment_method' => 'TonicsFlutterWave',
                        'tonics_solution' => PaymentSettingsController::TonicsSolution_TonicsCloud
                    ]),
                ]);

                if (isset($data['PURCHASE_RECORD'])){
                    $confirmFlutterWavePayment = new TonicsCloudConfirmFlutterWavePayment();
                    $confirmFlutterWavePayment->setData($data['PURCHASE_RECORD']);
                    job()->enqueue($confirmFlutterWavePayment);
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
     * @throws \Exception|\Throwable
     */
    public function generateInvoiceID(): void
    {
        response()->onSuccess(uniqid('TonicsCloud_', true));
    }
}