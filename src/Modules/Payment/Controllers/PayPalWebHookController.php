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

class PayPalWebHookController
{
    const PaymentType_AudioTonics = 'AudioTonics_';

    /**
     * @throws \Exception
     */
    public function handleWebHook()
    {
        // Retrieve the payload and headers of the webhook notification
        $entityBody = request()->getEntityBody();
        if (helper()->isJSON($entityBody)){
            $webhook = json_decode($entityBody);
            if (AudioTonicsPayPalHandler::verifyWebHookSignature($webhook)){
                $webHookEvent = $webhook->webhook_event;

            }
        }

       // $headers = request()->getHeaderByKey(['']);
    }
}