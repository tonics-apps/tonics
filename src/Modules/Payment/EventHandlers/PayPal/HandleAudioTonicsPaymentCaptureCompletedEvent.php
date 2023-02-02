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

use App\Modules\Payment\Controllers\PaymentSettingsController;
use App\Modules\Payment\Events\PayPal\OnAddPayPalWebHookEvent;
use App\Modules\Payment\Events\PayPal\PayPalWebHookEventInterface;
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

    public function TonicsSolutionType(): string
    {
        return PaymentSettingsController::TonicsSolution_AudioTonics;
    }

    public function HandleWebHookEvent(OnAddPayPalWebHookEvent $payPalWebHookEvent): void
    {
        // we handle the final event completion hia;
        dd('handle tonics hia', $payPalWebHookEvent);
    }
}