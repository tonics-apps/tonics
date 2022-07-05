<?php

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