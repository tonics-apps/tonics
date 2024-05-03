<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\Controllers;

use App\Modules\Payment\Events\AudioTonics\OnAddTrackPaymentEvent;
use App\Modules\Payment\Events\TonicsCloud\OnAddTonicsCloudPaymentEvent;
use App\Modules\Payment\Events\TonicsPaymentInterface;

class PaymentController
{

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function RequestFlow(): void
    {
        $paymentHandlerName = url()->getHeaderByKey('PaymentHandlerName');

        /** @var $paymentObject OnAddTonicsCloudPaymentEvent */
        $paymentObject = event()->dispatch(new OnAddTonicsCloudPaymentEvent())->event();
        if ($paymentObject->exist($paymentHandlerName)){
            /** @var $paymentHandler TonicsPaymentInterface */
            $paymentHandler = $paymentObject->getPaymentHandler($paymentHandlerName);
            $paymentHandler->handlePayment();
        } else {
            response()->onError(400, 'No Valid Payment Handler');
        }
    }
}