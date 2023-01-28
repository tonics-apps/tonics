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

    /**
     * @throws \Exception
     */
    public function handleWebHook()
    {
        // Retrieve the payload and headers of the webhook notification
        if (helper()->isJSON(request()->getEntityBody())){
            $webhookEvent = json_decode(request()->getEntityBody());
            AudioTonicsPayPalHandler::verifyWebHookSignature($webhookEvent);
        }

       // $headers = request()->getHeaderByKey(['']);
    }
}