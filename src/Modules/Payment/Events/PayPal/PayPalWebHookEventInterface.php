<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Events\PayPal;

interface PayPalWebHookEventInterface
{
    public function EventType(): string;

    /**
     * E.g AudioTonics, TonicsCommerce (not yet available), etc
     * @return string
     */
    public function TonicsSolutionType(): string;

    /**
     * @return void
     */
    public function HandleWebHookEvent(OnAddPayPalWebHookEvent $payPalWebHookEvent): void;
}