<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\EventHandlers\AudioTonicsPaymentHandler;

use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Events\AudioTonics\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\TonicsPaymentInterface;
use App\Modules\Payment\Jobs\AudioTonics\AudioTonicsConfirmPayPalPayment;
use App\Modules\Payment\Library\AudioTonicsHelper;
use App\Modules\Payment\Library\Helper;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class AudioTonicsPayPalHandler implements HandlerInterface, TonicsPaymentInterface
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
    public function enabled(): bool
    {
        return PaymentSettingsController::isEnabled(PaymentSettingsController::PayPal_Enabled);
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
            response()->onSuccess(Helper::PayPalPublicKey());
        }

        if ($queryType === self::Query_CapturedPaymentDetails) {
            try {
                $body = url()->getEntityBody();
                $body = json_decode($body);
                $data = AudioTonicsHelper::CapturePaymentDetails($body, function ($customerData, $purchaseInfo, $cartItemsSlugID) use ($body){
                    return [
                        'fk_customer_id' => $customerData->user_id,
                        'total_price' => $purchaseInfo['totalPrice'] ?? 0,
                        'others' => json_encode([
                            'downloadables' => $purchaseInfo['downloadables'] ?? [], // would be used to confirm the item the user is actually buying
                            // if this is different from register email, we would also send the order details there in case user misspell register email
                            'payment_email_address' => (isset($body->orderData->payer->email_address)) ? $body->orderData->payer->email_address : '',
                            'itemIds' => $cartItemsSlugID, // would be used to confirm the item the user is actually buying
                            'invoice_id' => $body->invoice_id,
                            'order_id' => $body->orderData->id, // this is for PayPal
                            'payment_method' => 'TonicsPayPal', // i.e PayPal, FlutterWave
                            'tonics_solution' => PaymentSettingsController::TonicsSolution_AudioTonics
                        ]),
                    ];
                });

                if (isset($data['PURCHASE_RECORD'])){
                    $ManuallyConfirmAudioTonicsPaymentPayPal = new AudioTonicsConfirmPayPalPayment();
                    $ManuallyConfirmAudioTonicsPaymentPayPal->setData($data['PURCHASE_RECORD']);
                    job()->enqueue($ManuallyConfirmAudioTonicsPaymentPayPal);
                }

                response()->onSuccess(['email' => $data['CHECKOUT_EMAIL']], $data['MESSAGE']);

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
        response()->onSuccess(uniqid('AudioTonics_', true));
    }
}