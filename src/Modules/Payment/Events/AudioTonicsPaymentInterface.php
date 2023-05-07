<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Payment\Events;

interface AudioTonicsPaymentInterface
{
    /**
     * Transporters Name
     * @return string
     */
    public function name(): string;

    /**
     * @return bool
     */
    public function enabled(): bool;

    /**
     * @return void
     */
    public function handlePayment(): void;
}