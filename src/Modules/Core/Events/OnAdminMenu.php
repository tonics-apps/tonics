<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

/**
 * Class OnAdminMenu
 * @package Modules\Core\Events
 */
class OnAdminMenu implements EventInterface
{
    /**
     * @return $this
     */
    public function event(): static
    {
        return $this;
    }
}
