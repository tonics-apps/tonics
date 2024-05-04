<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
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

namespace App\Modules\Payment\Events;

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