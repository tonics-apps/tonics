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

namespace App\Modules\Payment\Events\PayPal;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddPayPalWebHookEvent implements EventInterface
{
    private array $paypalWebHookHandler = [];
    private \stdClass|null $webHookData = null;

    const EventType_CheckoutOrderApproved = 'CHECKOUT.ORDER.APPROVED';
    const EventType_PaymentCapturedCompleted = 'PAYMENT.CAPTURE.COMPLETED';


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
        $this->paypalWebHookHandler[$payPalWebHookEventHandler->EventType()][] = $payPalWebHookEventHandler;
        return $this;
    }

    /**
     * This would run all paypalWebHookHandlers that is waiting to handle $eventType, then in each handler,
     * you can act on it based on whatever, e.g. invoice_id, etc
     * @param string $eventType
     * @return void
     */
    public function handleWebHookEvent(string $eventType): void
    {
        if ($this->eventTypeExist($eventType)){
            $paypalWebHookHandlers = $this->paypalWebHookHandler[$eventType];
            /** @var PayPalWebHookEventInterface $paypalWebHookHandler */
            foreach ($paypalWebHookHandlers as $paypalWebHookHandler){
                $paypalWebHookHandler->HandleWebHookEvent($this);
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function eventTypeExist(string $name): bool
    {
        return isset($this->paypalWebHookHandler[$name]);
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
     * @return \stdClass|null
     */
    public function getWebHookData(): ?\stdClass
    {
        return $this->webHookData;
    }

    /**
     * @param mixed $webHookData
     * @return OnAddPayPalWebHookEvent
     */
    public function setWebHookData(mixed $webHookData): OnAddPayPalWebHookEvent
    {
        $this->webHookData = $webHookData;
        return $this;
    }
}