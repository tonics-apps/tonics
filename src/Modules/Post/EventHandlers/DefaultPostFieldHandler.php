<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Post\EventHandlers;

use App\Modules\Post\Events\OnPostDefaultField;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DefaultPostFieldHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnPostDefaultField */
        $event->addDefaultField('post-page')->addDefaultField('seo-settings')
            ->addDefaultField('site-header', true)
            ->addDefaultField('site-footer', true)
            ->addDefaultField('sidebar-widget', true);
    }
}