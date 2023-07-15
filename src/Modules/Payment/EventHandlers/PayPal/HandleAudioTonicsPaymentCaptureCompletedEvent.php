<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\EventHandlers\PayPal;

use App\Modules\Payment\Events\PayPal\OnAddPayPalWebHookEvent;
use App\Modules\Payment\Events\PayPal\PayPalWebHookEventInterface;
use App\Modules\Payment\Library\AudioTonicsHelper;
use App\Modules\Payment\Library\HandleTonicsPaymentCapture;
use App\Modules\Payment\Library\PayPalPaymentCapturedCompletedWebHookResponse;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class HandleAudioTonicsPaymentCaptureCompletedEvent implements HandlerInterface, PayPalWebHookEventInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnAddPayPalWebHookEvent */
        $event->addWebHookEventHandler($this);
    }

    public function EventType(): string
    {
        return OnAddPayPalWebHookEvent::EventType_PaymentCapturedCompleted;
    }

    /**
     * @param OnAddPayPalWebHookEvent $payPalWebHookEvent
     * @return void
     * @throws \Exception|\Throwable
     */
    public function HandleWebHookEvent(OnAddPayPalWebHookEvent $payPalWebHookEvent): void
    {
        $capturedResponse = new PayPalPaymentCapturedCompletedWebHookResponse($payPalWebHookEvent->getWebHookData());
        # Only Handle AudioTonics Order
        $invoiceID = $capturedResponse->getInvoiceID();

        if ($capturedResponse->isCompleted()) {
            HandleTonicsPaymentCapture::validateTonicsTransactionAndPrepareOrderMail(
                ['invoice_id' => $invoiceID, 'total_amount' => $capturedResponse->getTotalAmount(), 'currency' => $capturedResponse->getCurrency() ],
                function ($purchaseRecord){
                    AudioTonicsHelper::DeliverOrderEmail($purchaseRecord);
                }
            );
        }

        response()->onSuccess([], 'success');
    }
}