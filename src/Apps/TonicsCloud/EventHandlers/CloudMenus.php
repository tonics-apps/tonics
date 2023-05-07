<?php
/*
 * Copyright (c) 2023. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers;

use App\Modules\Core\Events\OnAdminMenu;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class CloudMenus implements HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var OnAdminMenu $event */
    }
}