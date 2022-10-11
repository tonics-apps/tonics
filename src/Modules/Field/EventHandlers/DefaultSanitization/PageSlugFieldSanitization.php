<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Field\EventHandlers\DefaultSanitization;

use App\Modules\Field\Events\OnAddFieldSanitization;
use App\Modules\Field\Interfaces\FieldValueSanitizationInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class PageSlugFieldSanitization implements HandlerInterface, FieldValueSanitizationInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnAddFieldSanitization */
        $event->addField($this);
    }

    public function sanitizeName(): string
    {
        return 'PageSlug';
    }

    /**
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    public function sanitize($value): mixed
    {
        return helper()->slugForPage($value);
    }
}