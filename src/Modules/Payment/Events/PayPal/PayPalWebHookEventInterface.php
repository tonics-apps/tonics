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
     * @param OnAddPayPalWebHookEvent $payPalWebHookEvent
     * @return void
     */
    public function HandleWebHookEvent(OnAddPayPalWebHookEvent $payPalWebHookEvent): void;
}