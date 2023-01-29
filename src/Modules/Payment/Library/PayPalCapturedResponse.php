<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Library;

use stdClass;

class PayPalCapturedResponse {

    private stdClass $response;

    public function __construct(stdClass $response) {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response->id ?? null;
    }

    public function getStatus()
    {
        return $this->response->status ?? null;
    }

    public function isCompleted(): bool
    {
        return $this->response->status === 'COMPLETED';
    }

    public function isDeclined(): bool
    {
        return $this->response->status === 'DECLINED';
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->response->status === 'PARTIALLY_REFUNDED';
    }

    public function isPending(): bool
    {
        return $this->response->status === 'PENDING';
    }

    public function isRefunded(): bool
    {
        return $this->response->status === 'REFUNDED';
    }

    public function isFailed(): bool
    {
        return $this->response->status === 'FAILED';
    }

    public function getTotalAmount()
    {
        if (isset($this->response->purchase_units->{0}->amount)){
            return $this->response->purchase_units->{0}->amount->value ?? null;
        }

        return null;
    }

    public function getCurrency()
    {
        if (isset($this->response->purchase_units->{0}->amount)){
            return $this->response->purchase_units->{0}->amount->currency_code ?? null;
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
