<?php
/*
 * Copyright (c) 2022-2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Events\TonicsCloud;


use App\Modules\Payment\Events\TonicsPaymentInterface;
use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnAddTonicsCloudPaymentEvent implements EventInterface
{

    private array $payments = [];

    public function event(): static
    {
        return $this;
    }

    public function addPaymentHandler(TonicsPaymentInterface $trackPayment): static
    {
        $this->payments[strtolower($trackPayment->name())] = $trackPayment;
        return $this;
    }

    public function getPaymentsHooker(): string
    {
        $frag = '';
        /** @var TonicsPaymentInterface $payment */
        foreach ($this->payments as $name => $payment) {
            if ($payment->enabled()){
                $frag .= <<<HTML
<span class="d:none" data-trackPaymentHandler="$name"></span>
HTML;
            }
        }
        return $frag;
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