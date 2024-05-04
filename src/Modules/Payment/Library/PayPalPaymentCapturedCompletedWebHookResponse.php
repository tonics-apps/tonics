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

namespace App\Modules\Payment\Library;

use stdClass;

class PayPalPaymentCapturedCompletedWebHookResponse {

    private stdClass $webhookEvent;

    public function __construct(stdClass $webhookEvent) {
        $this->webhookEvent = $webhookEvent;
    }

    public function getWebhookId()
    {
        return $this->webhookEvent->id;
    }

    public function getOrderId()
    {
        if (isset($this->webhookEvent->resource->id)){
            return $this->webhookEvent->resource->id;
        }

        return null;
    }

    public function getStatus()
    {
        return $this->webhookEvent->resource->status ?? null;
    }

    public function isCompleted(): bool
    {
        if (isset($this->webhookEvent->resource->status)){
            return $this->webhookEvent->resource->status === 'COMPLETED';
        }
        return false;
    }

    public function isDeclined(): bool
    {
        if (isset($this->webhookEvent->resource->status)){
            return $this->webhookEvent->status === 'DECLINED';
        }
        return false;
    }

    public function isPartiallyRefunded(): bool
    {
        if (isset($this->webhookEvent->resource->status)){
            return $this->webhookEvent->resource->status === 'PARTIALLY_REFUNDED';
        }
        return false;
    }

    public function isPending(): bool
    {
        if (isset($this->webhookEvent->resource->status)){
            return $this->webhookEvent->resource->status === 'PENDING';
        }
        return false;
    }

    public function isRefunded(): bool
    {
        if (isset($this->webhookEvent->resource->status)){
            return $this->webhookEvent->resource->status === 'REFUNDED';
        }
        return false;
    }

    public function isFailed(): bool
    {
        if (isset($this->webhookEvent->resource->status)){
            return $this->webhookEvent->resource->status === 'FAILED';
        }
        return false;
    }

    public function getTotalAmount()
    {
        if (isset($this->webhookEvent->resource->amount)){
            return $this->webhookEvent->resource->amount->value ?? null;
        }

        return null;
    }

    public function getInvoiceID()
    {
        if (isset($this->webhookEvent->resource->invoice_id)){
            return $this->webhookEvent->resource->invoice_id;
        }

        return null;
    }

    public function getCurrency()
    {
        if (isset($this->webhookEvent->resource->amount)){
            return $this->webhookEvent->resource->amount->currency_code ?? null;
        }

        return null;
    }
}
