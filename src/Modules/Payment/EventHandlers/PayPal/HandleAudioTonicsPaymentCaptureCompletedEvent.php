<?php
/*
 *     Copyright (c) 2023-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
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