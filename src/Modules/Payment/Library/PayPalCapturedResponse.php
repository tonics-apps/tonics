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

class PayPalCapturedResponse {

    private stdClass $response;

    public function __construct(stdClass $response) {
        $this->response = $response;
    }

    public function getOrderID()
    {
        return $this->response->id ?? null;
    }

    public function getStatus()
    {
        return $this->response->status ?? null;
    }

    public function isCompleted(): bool
    {
        return $this->getStatus() === 'COMPLETED';
    }

    public function isDeclined(): bool
    {
        return $this->getStatus() === 'DECLINED';
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->getStatus() === 'PARTIALLY_REFUNDED';
    }

    public function isPending(): bool
    {
        return $this->getStatus() === 'PENDING';
    }

    public function isRefunded(): bool
    {
        return $this->getStatus() === 'REFUNDED';
    }

    public function isFailed(): bool
    {
        return $this->getStatus() === 'FAILED';
    }

    public function getTotalAmount()
    {
        if (isset($this->response->purchase_units[0]->amount)){
            return $this->response->purchase_units[0]->amount->value ?? null;
        }

        return null;
    }

    public function getTotalCountOfItemPurchased(): ?int
    {
        if (isset($this->response->purchase_units[0]->items)){
            return count($this->response->purchase_units[0]->items);
        }

        return null;
    }

    public function getItemsPurchased(): ?int
    {
        if (isset($this->response->purchase_units[0]->items)){
            return $this->response->purchase_units[0]->items;
        }

        return null;
    }

    public function getInvoiceID()
    {
        if (isset($this->response->purchase_units[0]->invoice_id)){
            return $this->response->purchase_units[0]->invoice_id;
        }

        return null;
    }

    public function getCurrency()
    {
        if (isset($this->response->purchase_units[0]->amount)){
            return $this->response->purchase_units[0]->amount->currency_code ?? null;
        }

        return null;
    }

    public function getPayerAddress()
    {
        return property_exists($this->response, 'payer') && property_exists($this->response->payer, 'address') ? $this->response->payer->address : null;
    }

    public function getPayerEmail()
    {
        return property_exists($this->response, 'payer') && property_exists($this->response->payer, 'email_address') ? $this->response->payer->email_address : null;
    }

    public function getPayerName()
    {
        return property_exists($this->response, 'payer') && property_exists($this->response->payer, 'name') ? $this->response->payer->name : null;
    }

    public function getPayerGivenName()
    {
        return property_exists($this->response, 'payer') && property_exists($this->response->payer, 'name') && property_exists($this->response->payer->name, 'given_name') ? $this->response->payer->name->given_name : null;
    }

    public function getPayerSurname()
    {
        return property_exists($this->response, 'payer') && property_exists($this->response->payer, 'name') && property_exists($this->response->payer->name, 'surname') ? $this->response->payer->name->surname : null;
    }
}
