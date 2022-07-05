<?php

namespace App\Modules\Post\Events;

use Devsrealm\TonicsEventSystem\Interfaces\EventInterface;


/**
 * This could be used to filter the $postData before post save or post update.
 */
class OnBeforePostSave implements EventInterface
{

    private array $data;

    public function __construct(array $postData = []){
        $this->data = $postData;
    }

    /**
     * @inheritDoc
     */
    public function event(): static
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}