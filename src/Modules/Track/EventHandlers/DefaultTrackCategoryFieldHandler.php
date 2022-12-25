<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Track\EventHandlers;

use App\Modules\Post\Events\OnPostCategoryDefaultField;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class DefaultTrackCategoryFieldHandler implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnPostCategoryDefaultField */
        $event->addDefaultField('track-category-page')->addDefaultField('seo-settings')
            ->addDefaultField('site-header', true)
            ->addDefaultField('site-footer', true)
            ->addDefaultField('sidebar-widget', true);
    }
}