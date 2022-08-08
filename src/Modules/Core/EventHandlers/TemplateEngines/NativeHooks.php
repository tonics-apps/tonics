<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\EventHandlers\TemplateEngines;

use App\Modules\Core\Events\OnSelectTonicsTemplateHooks;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class NativeHooks implements HandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        /** @var $event OnSelectTonicsTemplateHooks */
        $event->addMultipleHooks([
            'before_html', 'before_head', 'in_head_attribute', 'in_head', 'before_body', 'in_body_attribute', 'in_body', 'before_header',
            'in_header_attribute', 'in_header', 'after_header', 'before_closing_body', 'after_body', 'before_footer', 'in_footer_attribute', 'in_footer', 'after_footer'
        ]);
    }
}