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


use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddTrackPaymentEvent implements EventInterface
{

    private array $payments = [];

    public function event(): static
    {
        return $this;
    }

    public function addPaymentHandler(AudioTonicsPaymentInterface $trackPayment): static
    {
        $this->payments[strtolower($trackPayment->name())] = $trackPayment;
        return $this;
    }

    /**
     * @return array
     */
    public function getPayments(): array
    {
        return $this->payments;
    }

    public function exist(string $name): bool
    {
        $name = strtolower($name);
        return isset($this->payments[$name]);
    }

    /**
     * @throws \Exception
     */
    public function getPaymentHandler(string $name): mixed
    {
        $name = strtolower($name);
        if (isset($this->payments[$name])){
            return $this->payments[$name];
        }

        throw new \Exception("$name is an unknown payment handler name");
    }
    /**
     * @param array $payments
     */
    public function setPayments(array $payments): void
    {
        $this->payments = $payments;
    }
}