<?php
/*
 * Copyright (c) 2024. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Apps\TonicsCloud\EventHandlers\Fields\Sanitization;

use App\Apps\TonicsCloud\Interfaces\CloudAppInterface;
use App\Modules\Field\Interfaces\FieldValueSanitizationInterface;
use Devsrealm\TonicsEventSystem\Interfaces\HandlerInterface;

class RenderTonicsCloudDefaultContainerVariablesStringSanitization implements HandlerInterface, FieldValueSanitizationInterface
{

    /**
     * @inheritDoc
     */
    public function handleEvent(object $event): void
    {
        $event->addField($this);
    }

    public function sanitizeName(): string
    {
        return 'TonicsCloudRenderDefaultContainerVariables';
    }

    public function sanitize($value): string
    {
        return CloudAppInterface::replaceVariables($value, [
            "[[RAND_STRING_RENDER]]" => function() { return bin2hex(random_bytes(30)); },
                // add more here
            ]
        );
    }
}