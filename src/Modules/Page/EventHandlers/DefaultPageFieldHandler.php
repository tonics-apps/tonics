<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Page\EventHandlers;

use App\Modules\Post\Events\OnPostDefaultField;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DefaultPageFieldHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnPostDefaultField */
        $event->addDefaultField('default-page-field')
            ->addDefaultField('site-header', true)
            ->addDefaultField('site-footer', true);
    }
}