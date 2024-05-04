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