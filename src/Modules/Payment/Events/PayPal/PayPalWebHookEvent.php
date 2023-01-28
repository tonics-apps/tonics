<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Events\PayPal;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class PayPalWebHookEvent implements EventInterface
{
    private array $paypalWebHookHandler = [];
    private string $invoiceID = '';

    const EventType_CheckoutOrderApproved = 'CHECKOUT.ORDER.APPROVED';
    const EventType_PaymentCapturedCompleted = 'PAYMENT.CAPTURE.COMPLETED';

    const TonicsSolution_AudioTonics = 'AudioTonics';
    const TonicsSolution_TonicsCommerce = 'TonicsCommerce'; // not yet available


    public function event(): static
    {
        return $this;
    }

    /**
     * @param PayPalWebHookEventInterface $payPalWebHookEventHandler
     * @return $this
     */
    public function addWebHookEventHandler(PayPalWebHookEventInterface $payPalWebHookEventHandler): static
    {
        $this->paypalWebHookHandler[$payPalWebHookEventHandler->EventType()][$payPalWebHookEventHandler->TonicsSolutionType()] = $payPalWebHookEventHandler;
        return $this;
    }

    /**
     * @param string $eventType
     * @param string $tonicsSolutionType
     * @return void
     */
    public function handleWebHookEvent(string $eventType, string $tonicsSolutionType): void
    {
        if ($this->eventTypeExist($eventType, $tonicsSolutionType)){
            $paypalWebHookHandler = $this->paypalWebHookHandler[$eventType][$tonicsSolutionType];
            /** @var PayPalWebHookEventInterface $paypalWebHookHandler */
            $paypalWebHookHandler->HandleWebHookEvent($this);
        }
    }

    /**
     * @param string $name
     * @param string $tonicsSolutionType
     * @return bool
     */
    public function eventTypeExist(string $name, string $tonicsSolutionType): bool
    {
        return isset($this->paypalWebHookHandler[$name][$tonicsSolutionType]);
    }

    /**
     * @return array
     */
    public function getPaypalWebHookHandler(): array
    {
        return $this->paypalWebHookHandler;
    }

    /**
     * @param array $paypalWebHookHandler
     */
    public function setPaypalWebHookHandler(array $paypalWebHookHandler): void
    {
        $this->paypalWebHookHandler = $paypalWebHookHandler;
    }

    /**
     * @return string
     */
    public function getInvoiceID(): string
    {
        return $this->invoiceID;
    }

    /**
     * @param string $invoiceID
     * @return PayPalWebHookEvent
     */
    public function setInvoiceID(string $invoiceID): PayPalWebHookEvent
    {
        $this->invoiceID = $invoiceID;
        return $this;
    }
}