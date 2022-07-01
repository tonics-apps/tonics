<?php

namespace App\Modules\Field\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

/**
 * Map a Field To a Handler
 */
class FieldTemplateFile implements EventInterface
{

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }
}