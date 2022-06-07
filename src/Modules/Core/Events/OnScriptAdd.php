<?php

namespace App\Modules\Core\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;

class OnScriptAdd implements \Devsrealm\TonicsEventSystem\Interfaces\EventInterface
{

    private array $scriptSettings = [];

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }
}