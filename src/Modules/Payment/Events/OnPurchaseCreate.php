<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Events;

use App\Modules\Post\Events\AbstractClasses\PostDataAccessor;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnPurchaseCreate implements EventInterface
{
    private \stdClass $purchase;

    public function __construct(\stdClass $purchase)
    {
        $this->purchase = $purchase;
        if (isset($purchase->created_at)){
            $this->purchase->created_at = $this->getCreatedAt();
        }
        if (isset($purchase->updated_at)){
            $this->purchase->updated_at = $this->getUpdatedAt();
        }
    }

    public function getAll(): \stdClass
    {
        return $this->purchase;
    }

    public function getAllToArray(): array
    {
        return (array)$this->purchase;
    }

    public function getPurchaseID(): string|int
    {
        return (property_exists($this->purchase, 'purchase_id')) ? $this->purchase->purchase_id : '';
    }

    public function getSlugID(): mixed
    {
        return (property_exists($this->purchase, 'slug_id')) ? $this->purchase->slug_id : '';
    }

    public function getPurchaseFkCustomerID(): mixed
    {
        return (property_exists($this->purchase, 'fk_customer_id')) ? $this->purchase->fk_customer_id : '';
    }

    public function getPurchaseTotalPrice(): mixed
    {
        return (property_exists($this->purchase, 'total_price')) ? $this->purchase->total_price : '';
    }

    public function getPurchasePaymentStatus(): mixed
    {
        return (property_exists($this->purchase, 'payment_status')) ? $this->purchase->payment_status : '';
    }

    public function getPurchaseOthers(): mixed
    {
        return (property_exists($this->purchase, 'others')) ? json_decode($this->purchase->others) : '';
    }

    public function getPurchaseInvoiceID(): mixed
    {
        return (property_exists($this->purchase, 'invoice_id')) ? $this->purchase->invoice_id : '';
    }

    public function getCreatedAt(): string
    {
        return (property_exists($this->purchase, 'created_at')) ? str_replace(' ', 'T', $this->purchase->created_at) : '';
    }

    public function getUpdatedAt(): string
    {
        return (property_exists($this->purchase, 'updated_at')) ? str_replace(' ', 'T', $this->purchase->updated_at) : '';
    }


    public function event(): static
    {
        return $this;
    }

    /**
     * @return \stdClass
     */
    public function getPurchase(): \stdClass
    {
        return $this->purchase;
    }
}