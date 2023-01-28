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

use App\Modules\Payment\EventHandlers\TrackPaymentMethods\AudioTonicsPayPalHandler;
use App\Modules\Payment\Events\PayPal\PayPalWebHookEvent;

class PayPalWebHookController
{
    const PaymentType_AudioTonics = 'AudioTonics_';

    /**
     * @throws \Exception
     */
    public function handleWebHook()
    {
        $entityBody = request()->getEntityBody();
        if (helper()->isJSON($entityBody)){
            $webhook = json_decode($entityBody);
            if (AudioTonicsPayPalHandler::verifyWebHookSignature($webhook)){
                $webHookEvent = $webhook->webhook_event;
                $eventType = $webHookEvent->event_type;
                if (isset($webHookEvent->resource->purchase_units->{0}->invoice_id)){
                    $invoiceIDString = $webHookEvent->resource->purchase_units->{0}->invoice_id;
                    $invoiceID = explode('_', $invoiceIDString);
                    $tonicsSolutionType = $invoiceID[0] ?? '';

                    /** @var $webHookEventObject PayPalWebHookEvent */
                    $payPalWebHookEventObject = new PayPalWebHookEvent();
                    $payPalWebHookEventObject->setInvoiceID($invoiceIDString);

                    $webHookEventObject = event()->dispatch($payPalWebHookEventObject)->event();
                    $webHookEventObject->handleWebHookEvent($eventType, $tonicsSolutionType);
                }
            }
        }

        response()->onError(400);
    }
}