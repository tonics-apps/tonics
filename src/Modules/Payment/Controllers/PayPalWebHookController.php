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

use App\Modules\Payment\Events\PayPal\OnAddPayPalWebHookEvent;

class PayPalWebHookController
{
    /**
     * @throws \Exception
     */
    public function handleWebHook(): void
    {
        $entityBody = request()->getEntityBody();
        if (helper()->isJSON($entityBody)){
            $webhook = json_decode($entityBody);
            if (PaymentSettingsController::verifyWebHookSignature($webhook)){
                $eventType = $webhook->event_type;
                /** @var $webHookEventObject OnAddPayPalWebHookEvent */
                $payPalWebHookEventObject = new OnAddPayPalWebHookEvent();
                $payPalWebHookEventObject->setWebHookEvent($webhook);
                $webHookEventObject = event()->dispatch($payPalWebHookEventObject)->event();
                $webHookEventObject->handleWebHookEvent($eventType);

                response()->onSuccess([], 'On Success');
            }
        }

        response()->onError(400);
    }
}