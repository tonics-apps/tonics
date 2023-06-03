<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsAI\EventHandlers;

use App\Modules\Field\Events\OnEditorFieldSelection;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class TonicsAIOpenAIChatFieldSelection implements HandlerInterface
{

    public function handleEvent(object $event): void
    {
        /** @var $event OnEditorFieldSelection */
        $event->addField('Chat', 'app-tonicsai-openai-chat', category: 'TonicsAI');
    }
}