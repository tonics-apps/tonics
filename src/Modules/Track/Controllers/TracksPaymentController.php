<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\Controllers;

use App\Modules\Payment\Events\AudioTonicsPaymentInterface;
use App\Modules\Payment\Events\OnAddTrackPaymentEvent;

class TracksPaymentController
{

    /**
     * @throws \Exception
     */
    function getRequestFlow()
    {
        $paymentHandlerName = url()->getHeaderByKey('PaymentHandlerName');

        /** @var $paymentObject OnAddTrackPaymentEvent */
        $paymentObject = event()->dispatch(new OnAddTrackPaymentEvent())->event();
        if ($paymentObject->exist($paymentHandlerName)){
            /** @var $paymentHandler AudioTonicsPaymentInterface */
            $paymentHandler = $paymentObject->getPaymentHandler($paymentHandlerName);
            $paymentHandler->handlePayment();
        } else {
            response()->onError(400, 'No Valid Payment Handler');
        }
    }

    function postRequestFlow() {}
}